<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LlmClient
{
    /**
     * Propose content-aligned chapters from a transcript.
     *
     * @param array<int, array{start: int, end: int, text: string}> $transcript
     * @return array<int, array{start: int, title: string}>
     */
    public function proposeChapters(array $transcript, int $duration): array
    {
        $transcriptText = collect($transcript)
            ->map(fn ($segment) => sprintf('[%d:%02d] %s', floor($segment['start'] / 60), $segment['start'] % 60, $segment['text']))
            ->implode("\n");

        $maxChapters = min(20, max(1, (int) ceil($duration / 300)));

        $prompt = <<<TEXT
        You segment audio recordings (often church services / sermons) into chapters for listeners.
        Return ONLY JSON: {"chapters":[{"start":<seconds int>,"title":"<short title>"}]}.

        Group content into coherent sections — prefer fewer, broader chapters over many small ones:
        - Consecutive singing, worship, hymns, or music MUST be ONE chapter titled for the whole block (e.g., "Song service", "Worship set"). Do NOT make a separate chapter per song.
        - Each chapter covers one coherent section (e.g., Announcements, Scripture reading, Prayer, Sermon, Benediction).
        - A long sermon may use a few chapters for genuine topic shifts, but do not over-segment.

        Rules:
        - Produce between 1 and {$maxChapters} chapters, aligned to ACTUAL content/topic changes (never even time splits).
        - The first chapter MUST start at 0.
        - "start" is an integer number of seconds, must be < {$duration}, and each must be unique.
        - Titles: short, descriptive, non-empty (<= 60 chars).

        Transcript (start time in [m:ss], then text):
        {$transcriptText}
        TEXT;

        $url = rtrim((string) config('services.llm.base_url'), '/').'/chat/completions';
        $payload = [
            'model' => config('services.llm.model'),
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant that outputs only JSON.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        // Retry on rate limits (429) with backoff; other errors fail immediately.
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

        $content = (string) $response->json('choices.0.message.content', '');
        $decoded = $this->extractJson($content);

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
