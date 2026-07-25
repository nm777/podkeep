# Contract: Chapter Generation Pipeline (Story 2)

**Branch**: `016-chapter-markers` | **Date**: 2026-07-24

How an automatic, content-aware chapter proposal is produced: transcription (local) + topic segmentation (LLM), on a dedicated low-priority queue. See research.md R3/R7/R8/R9.

## Trigger

`POST /library/{library_item}/chapters/generate` → `ChapterController@generate` (web/Inertia, behind `auth`/`verified`/`approved`). Authorizes ownership, requires `media_file.duration` (processed), then:

1. Sets `media_file.chapter_generation_status = 'pending'`, clears any prior `chapter_generation_error`.
2. Dispatches the job chain on the **`chapters` queue**.
3. Redirects back; the editor polls status.

## Queue: dedicated + low priority

Both jobs run with `->onQueue('chapters')` — **separate from the `default` queue** that handles uploads/downloads/feed jobs. The database queue driver has no native priority weighting, so "low priority" = a **dedicated `chapters`-queue worker** in production (single concurrency, optionally OS-`nice`d). This isolates the CPU-bound transcription so it never blocks routine operations (SC-008). The app dispatches to `chapters`; running that worker is a production deployment step (user-driven, per AGENTS.md).

## Jobs (chained on the `chapters` queue)

1. **`TranscribeMediaFile`** (expensive, CPU) — if `media_file.transcript` is missing:
   - For **video** media, first extract a 16 kHz mono WAV via ffmpeg (already in the image); whisper.cpp ingests audio only.
   - Shell out to the **whisper.cpp** binary (in the production image, default model `small.en`, path configurable) against that audio.
   - Parse timestamped output into `{start, end, text}[]` and store as `media_file.transcript`.
   - Set `chapter_generation_status = 'processing'`.
2. **`SegmentTranscriptIntoChapters`** (quick LLM call):
   - Send the transcript segments to the **OpenAI-compatible LLM** (`LlmClient`, provider-configurable — R7) with a JSON-mode prompt: return `[{start, title}]`, ≤20, content-aligned, sermon-structure-aware, first `start` at 0.
   - Sanitize server-side: cap 20, clamp `start` to `[0, duration)`, drop duplicates/blanks, sort ascending.
   - Store as `media_file.chapter_proposal`; set `chapter_generation_status = 'completed'`.

Chaining means a segmentation failure does **not** re-trigger transcription (the transcript is already saved). On any exception, set `chapter_generation_status = 'failed'` + `chapter_generation_error` (message); no chapters are published.

## LLM provider (configurable, OpenAI-compatible)

`config/services.php` → `llm => [base_url, api_key, model]`, sourced from `.env` (`LLM_BASE_URL`, `LLM_API_KEY`, `LLM_MODEL`). z.ai is used via its OpenAI-compatible endpoint; switching providers is an env-only change (satisfies "control to switch providers"). A small `LlmClient` wraps `Http::post("{$base_url}/chat/completions")` with `response_format: json`. Response parsing is defensive (extract JSON even if the model wraps it in prose).

## State & visibility (testable)

- `chapter_generation_status` transitions: `null → pending → processing → completed | failed`.
- `chapter_proposal` is populated only on `completed`; it is **never** read by RSS rendering.
- The editor polls status and, on `completed`, loads the proposal as editable drafts (saving publishes via the sync endpoint).
- Re-generation: sets status back to `pending` and overwrites `chapter_proposal` on completion; reuses the stored `transcript` (skips re-transcription).

## Failure & edge behavior

- Transcription failure (bad/missing audio, whisper.cpp error) → `failed` + error; user can retry.
- LLM failure (network, parse, bad JSON) → `failed` + error; transcript is retained so retry skips transcription.
- Audio with little/no speech → sparse/empty transcript → proposal may be empty; user falls back to manual authoring (Story 1). No failure.
- A generation already in progress → `generate` is a no-op (or rejects) until the current one settles; the button is disabled client-side.
- **Media re-downloaded/replaced** (different audio): the cached `transcript` and `chapter_proposal` are cleared (alongside FR-011's duration-change handling), so the next proposal re-transcribes the new audio.

## Deployment prerequisites (user-driven)

- Production image includes **whisper.cpp + model** (alongside yt-dlp/ffmpeg).
- Production runs a **`chapters`-queue worker** (e.g., `php artisan queue:work --queue=chapters`), ideally low-concurrency / `nice`d.
- `.env` provides `LLM_BASE_URL`, `LLM_API_KEY`, `LLM_MODEL`.

I will not modify Dockerfiles or production workers (AGENTS.md); the app code dispatches to the `chapters` queue and the generate route works once the worker + whisper.cpp are present.
