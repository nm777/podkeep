<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LlmClient
{
    /**
     * Propose content-aligned chapters from a transcript.
     *
     * Long transcripts are split into context-sized sections, segmented independently,
     * then merged — so this works on any model regardless of its context window.
     *
     * @param  array<int, array{start: int, end: int, text: string}>  $transcript
     * @return array<int, array{start: int, title: string}>
     */
    public function proposeChapters(array $transcript, int $duration): array
    {
        $sections = $this->splitIntoSections($transcript);

        // Distribute the global chapter cap (20) across sections so a long book
        // gets chapters throughout, not just the first part.
        $perSectionMax = max(1, (int) ceil(20 / max(1, count($sections))));

        $proposed = [];
        foreach ($sections as $i => $section) {
            $proposed = array_merge($proposed, $this->proposeForSection($section, $duration, $i === 0, $perSectionMax));
        }

        return $this->mergeChapters($proposed, $duration);
    }

    /**
     * Group consecutive transcript segments into sections under a character budget
     * (~tokens), so each fits any model's context window. Never splits mid-segment.
     *
     * @param  array<int, array{start: int, end: int, text: string}>  $transcript
     * @return array<int, array<int, array{start: int, end: int, text: string}>>
     */
    protected function splitIntoSections(array $transcript): array
    {
        $budget = (int) config('services.llm.section_chars', 24000);

        $sections = [];
        $current = [];
        $currentLen = 0;

        foreach ($transcript as $segment) {
            $len = strlen((string) $segment['text']);
            if ($currentLen + $len > $budget && $current !== []) {
                $sections[] = $current;
                $current = [];
                $currentLen = 0;
            }
            $current[] = $segment;
            $currentLen += $len;
        }

        if ($current !== []) {
            $sections[] = $current;
        }

        return $sections === [] ? [[]] : $sections;
    }

    /**
     * Ask the LLM for chapters within one section. Timestamps in the prompt are absolute,
     * so returned chapter starts are already absolute.
     *
     * @param  array<int, array{start: int, end: int, text: string}>  $section
     * @return array<int, array{start: int, title: string}>
     */
    protected function proposeForSection(array $section, int $duration, bool $isFirst, int $maxForSection): array
    {
        $text = collect($section)
            ->map(fn ($s) => sprintf('[%d:%02d] %s', floor($s['start'] / 60), $s['start'] % 60, $s['text']))
            ->implode("\n");

        $scope = $isFirst ? 'This is the full recording.' : 'This is one section of a longer recording.';
        $firstRule = $isFirst
            ? 'The first chapter MUST start at 0.'
            : "Only create a chapter for a genuine topic change WITHIN this section; do NOT create one at the section's beginning unless the content truly begins a new topic there.";

        $prompt = <<<TEXT
        You segment audio recordings (often church services / sermons) into chapters for listeners.
        Return ONLY JSON: {"chapters":[{"start":<seconds int>,"title":"<short title>"}]}.
        {$scope}

        Group content into coherent sections — prefer fewer, broader chapters over many small ones:
        - Consecutive singing, worship, hymns, or music MUST be ONE chapter titled for the whole block (e.g., "Song service", "Worship set"). Do NOT make a separate chapter per song.
        - Each chapter covers one coherent section.

        {$firstRule}
        - Produce at most {$maxForSection} chapter(s) for genuine topic changes in this section.
        - "start" is an integer number of seconds (absolute time), must be < {$duration}, and unique.
        - Titles: short, descriptive, non-empty (<= 60 chars).

        Transcript (start time in [m:ss], then text):
        {$text}
        TEXT;

        return $this->callLlm($prompt);
    }

    /**
     * Merge per-section chapters: sort by start, drop duplicates within an epsilon
     * (boundary artifacts where adjacent sections both flag ~the same time), and cap at 20.
     *
     * @param  array<int, array{start: mixed, title: mixed}>  $chapters
     * @return array<int, array{start: int, title: string}>
     */
    protected function mergeChapters(array $chapters, int $duration): array
    {
        usort($chapters, fn ($a, $b) => ($a['start'] ?? 0) <=> ($b['start'] ?? 0));

        $epsilon = 60;
        $merged = [];
        $lastStart = -PHP_INT_MAX;

        foreach ($chapters as $chapter) {
            $start = (int) ($chapter['start'] ?? 0);
            $title = trim((string) ($chapter['title'] ?? ''));

            if ($title === '') {
                continue;
            }
            if ($start < 0 || ($duration > 0 && $start >= $duration)) {
                continue;
            }
            if ($start - $lastStart < $epsilon) {
                continue; // too close to the previous kept chapter
            }

            $merged[] = ['start' => $start, 'title' => $title];
            $lastStart = $start;
        }

        if ($merged === [] || $merged[0]['start'] > 0) {
            array_unshift($merged, ['start' => 0, 'title' => 'Beginning']);
        }

        return array_slice($merged, 0, 20);
    }

    /**
     * Perform one chat-completion call and return its chapters array. Retries 429s.
     *
     * @return array<int, array{start: mixed, title: mixed}>
     */
    protected function callLlm(string $prompt): array
    {
        $url = rtrim((string) config('services.llm.base_url'), '/').'/chat/completions';
        $payload = [
            'model' => config('services.llm.model'),
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant that outputs only JSON.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        $response = null;
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $response = Http::withToken(config('services.llm.api_key'))->timeout(120)->post($url, $payload);

            if ($response->successful() || $response->status() !== 429) {
                break;
            }

            if ($attempt < 3) {
                sleep($attempt * 3); // 3s, 6s
            }
        }

        if (! $response->successful()) {
            $reason = $response->json('error.message')
                ?? $response->json('error.code')
                ?? Str::limit((string) $response->body(), 300);

            throw new \RuntimeException('LLM request failed: '.$response->status().' - '.trim((string) $reason));
        }

        $decoded = $this->extractJson((string) $response->json('choices.0.message.content', ''));

        return $decoded['chapters'] ?? [];
    }

    /**
     * Extract the JSON object from the model output (tolerant of code fences / prose).
     *
     * @return array<string, mixed>
     */
    protected function extractJson(string $content): array
    {
        $content = trim($content);
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $content = $matches[0];
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }
}
