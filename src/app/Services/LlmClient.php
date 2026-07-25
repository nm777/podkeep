<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
        You segment podcast/audio transcripts into topical chapters for listeners.
        Return ONLY JSON: {"chapters":[{"start":<seconds int>,"title":"<short title>"}]}.
        Rules:
        - Produce between 1 and {$maxChapters} chapters, aligned to the ACTUAL content/topic changes (not even time splits).
        - The first chapter MUST start at 0.
        - "start" is an integer number of seconds, must be < {$duration}, and each must be unique.
        - Titles must be short, descriptive, and non-empty (<= 60 chars).

        Transcript (start time in [m:ss], then text):
        {$transcriptText}
        TEXT;

        $response = Http::withToken(config('services.llm.api_key'))
            ->timeout(120)
            ->post(rtrim((string) config('services.llm.base_url'), '/').'/chat/completions', [
                'model' => config('services.llm.model'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant that outputs only JSON.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('LLM request failed: '.$response->status());
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
