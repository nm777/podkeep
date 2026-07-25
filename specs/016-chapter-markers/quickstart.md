# Quickstart: Media Chapter Markers

**Branch**: `016-chapter-markers` | **Spec**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Dev-oriented snapshot. Read before `/speckit.tasks`.

## What you're building

1. **Author & publish chapters (P1):** up to 20 chapters (start time + title) on a media file, edited from the dashboard "Edit Media" sheet once processed; chapters render in RSS via Podlove Simple Chapters (`psc:chapters`).
2. **Content-aware proposal (P2):** "Generate chapters from content" transcribes locally (**whisper.cpp**) + segments via an OpenAI-compatible **LLM** (z.ai, env-switchable) → ≤20 sermon-structure chapter drafts for review. Runs as chained jobs on a **dedicated low-priority `chapters` queue**.
3. **In-app player chapters (P3, deferrable):** seekable chapter list in `media-player.tsx`.

Chapters attach to **MediaFile** (shared by duplicate items). Proposals are drafts — never published until the user saves.

## Deployment prerequisites (user-driven — AGENTS.md)

- Production image includes **whisper.cpp + model** (default `small.en`) alongside yt-dlp/ffmpeg.
- Production runs a **`chapters`-queue worker**: `php artisan queue:work --queue=chapters` (low concurrency, optionally `nice`d).
- `.env` sets `LLM_BASE_URL`, `LLM_API_KEY`, `LLM_MODEL` (z.ai's OpenAI-compatible endpoint today).

The app dispatches to the `chapters` queue; without that worker, generate requests stay `pending`.

## Files touched

**Backend (do first):**
- `database/migrations/2026_07_24_..._create_chapters_table.php` — `media_file_id` FK (cascade), `start_time` int, `title` str; unique `(media_file_id, start_time)`.
- `database/migrations/2026_07_24_..._add_chapter_generation_to_media_files.php` — nullable `transcript` json, `chapter_generation_status` string, `chapter_proposal` json, `chapter_generation_error` text.
- `app/Models/Chapter.php` (new; `belongsTo MediaFile`); `app/Models/MediaFile.php` (`hasMany chapters` + generation-state casts).
- `app/Http/Requests/ChapterSyncRequest.php` — `chapters` array `max:20`, per-item `start_time` integer `≥0 < duration`, `title` required ≤255, no dup `start_time`.
- `app/Http/Controllers/ChapterController.php` — `sync()` (replace + RSS-cache clear) and `generate()` (set status, dispatch chain on `chapters` queue).
- `app/Jobs/TranscribeMediaFile.php` (whisper.cpp → `transcript`, skip if present), `app/Jobs/SegmentTranscriptIntoChapters.php` (LLM → sanitize → `chapter_proposal`). Both `->onQueue('chapters')`.
- `app/Services/Transcription/WhisperClient.php` (extract 16 kHz WAV via ffmpeg for video, then shell to whisper.cpp), `app/Services/LlmClient.php` (OpenAI-compatible JSON-mode call).
- `app/Policies/ChapterPolicy.php` (owner-only).
- `config/services.php` — `llm => [base_url, api_key, model]`.
- `routes/web.php` — `PUT library/{library_item}/chapters` (sync) + `POST library/{library_item}/chapters/generate`.
- `app/Http/Controllers/RssController.php` — eager-load `…mediaFile.chapters`.
- `resources/views/rss.blade.php` — `xmlns:psc`; per-item `<psc:chapters>` when chapters exist.

**Frontend:**
- `resources/js/types/index.d.ts` — `Chapter`; `MediaFile.chapters/transcript/chapter_generation_status/chapter_proposal/chapter_generation_error`.
- `resources/js/components/chapter-editor.tsx` — list + "Generate from content" (POST → poll status → load proposal) + add/remove/edit/save.
- `resources/js/pages/dashboard.tsx` — "Edit Media" sheet gains a Chapters section (only when `media_file.duration`).
- `resources/js/components/media-player.tsx` — P3: chapter list + seek via ref.

## Reuse, don't rebuild

- **Sync pattern:** copy `FeedController::syncFeedItems`. **Cache clear:** copy the `Cache::forget("rss.{$feedId}")` loop from `LibraryController`.
- **Queue dispatch:** chain jobs like existing `dispatch(new …)`, but add `->onQueue('chapters')`.
- **Polling:** reuse the dashboard's 5s `router.reload` pattern for generation status.
- **LLM client:** `Http::withToken(config('services.llm.api_key'))->post("{$base}/chat/completions", …)`; `response_format: json`.

## Key behavior rules (must hold)

- Hard cap 20 chapters/media file (server-enforced).
- `start_time` ∈ [0, duration); duplicates rejected; titles non-empty; sorted ascending.
- Editing chapters on a shared file updates every feed/episode using that file.
- **Proposals never auto-publish** — only `Save` writes to `chapters` (and thus RSS).
- Generation is **idempotent on the transcript**: re-proposal reuses `transcript`, skips re-transcription.
- **Re-download/replacement** clears `transcript` + `chapter_proposal` (different audio) alongside the FR-011 duration checks.
- **Video** is transcribed by extracting audio via ffmpeg first (whisper.cpp is audio-only).
- RSS stays valid XML (the `RssController` DOMDocument check enforces this).
- `chapters` queue is isolated from `default` (transcription never blocks uploads/downloads).

## How to run the tooling (ephemeral containers — AGENTS.md)

```bash
# Pest (SQLite :memory:)
docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html \
  --entrypoint php podkeep-app:latest artisan test --compact tests/Feature/ChapterManagementTest.php

# PHPStan / Pint
docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html \
  --entrypoint vendor/bin/phpstan podkeep-app:latest analyse --no-progress
docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html \
  --entrypoint vendor/bin/pint podkeep-app:latest --dirty

# fallow (JS) — mount repo root so git diff works
docker run --rm -v /home/nate/src/podkeep:/repo -w /repo/src --entrypoint sh node:22 \
  -c 'git config --global --add safe.directory /repo && ./node_modules/.bin/fallow audit --base main'
```

## Verify by hand

1. Add a long media item, wait for processing → Edit Media → Chapters section visible.
2. Click **"Generate chapters from content"** → status shows progress → on completion, content-aligned chapter drafts appear (≤20).
3. Edit titles/times → **Save** → `chapters` written → RSS item contains `<psc:chapters>`.
4. 21st chapter blocked; `start_time` ≥ duration rejected; blank title rejected.
5. Clear all → `<psc:chapters>` disappears from the feed.
6. Re-generate → reuses the transcript (fast); overwrites the proposal; doesn't touch saved chapters until you Save again.
7. While a generation runs, confirm an upload/download still proceeds on the default queue (not blocked).

## Test coverage targets (constitution: TDD)

- `PUT /library/{library_item}/chapters` replaces the full set; deletes chapters not in payload.
- Cap: 21 rejected; `start_time` ≥ duration rejected; 0 allowed; duplicate `start_time` rejected; blank title rejected; non-owner 403.
- RSS: chaptered item emits `<psc:chapters>` (`H:MM:SS`); unchaptered emits none; feed stays valid XML.
- Saving chapters clears `rss.{feed_id}` cache.
- `POST …/chapters/generate` sets status `pending` and dispatches the chain on the `chapters` queue (assert `Queue::fake` / `onQueue('chapters')`).
- Job status transitions `pending→processing→completed` (or `failed` + error); on `completed`, `chapter_proposal` is sanitized (≤20, clamped, sorted, deduped) and is **not** in the `chapters` table.
- Re-generate reuses an existing `transcript` (transcription job skips).
