# Phase 0 Research: Media Chapter Markers

**Branch**: `016-chapter-markers` | **Date**: 2026-07-24

No `NEEDS CLARIFICATION` markers were carried from the spec — every ambiguity was defaulted per the project's "choose the most likely and continue" rule. Two decisions were confirmed with the user during planning: the **automatic proposal** (Story 2) is **content-aware via transcription + topic segmentation** (revised from a simple even-split), running on a **dedicated low-priority queue**. This file records the technical decisions, including one refinement to a spec assumption (R1).

## R1 — What do chapters attach to: MediaFile or LibraryItem?

**Decision**: Chapters attach to **`MediaFile`** (refines the spec assumption, which left it open).

**Rationale**: `MediaFile` holds the audio/video content and the `duration`; a chapter (and the transcript used to generate it) is a property of that content, exactly like duration. Duplicate detection (`media_files.file_hash`) lets **multiple LibraryItems share one MediaFile**. Putting chapters and transcripts on MediaFile means identical audio always has identical chapters (correct), and the duration/transcript needed to validate and generate chapters live on the same record. The editor is reached via a LibraryItem (`LibraryItem → mediaFile → chapters`).

**Consequence**: editing chapters on any library item that shares a file updates the chapters for all library items sharing that file (same audio). Desired behavior.

**Alternatives**: chapters on `LibraryItem` — redundant/wrong for duplicates and separates them from `duration`. Rejected.

## R2 — RSS chapter format

**Decision**: Inline **Podlove Simple Chapters** (`xmlns:psc="http://podlove.org/simple-chapters"`), `<psc:chapters>` with `<psc:chapter start="H:MM:SS" title="…"/>` per item, emitted only when the media file has ≥1 chapter.

**Rationale**: Self-contained (no extra route/JSON), cached-feed-friendly, widely supported. `RssController` already re-validates XML with `DOMDocument` and throws on malformation, so inline chapters can't silently break the feed. Constitution already expects podcast namespaces. `start_time` (integer seconds) → `H:MM:SS`.

**Alternatives**: Podcasting 2.0 `<podcast:chapters>` external JSON (modern but needs a JSON-serving route + cache) — documented upgrade path. Rejected for now.

## R3 — Automatic proposal: transcription + topic segmentation (revised from even-split)

**Decision (per user)**: The on-demand proposal **transcribes the audio on the PodKeep server, then runs topic segmentation on the transcript via a language model** to produce content-aligned chapters (≤20) as editable drafts. **No longer an even time-split.**

**Pipeline** (all on the dedicated `chapters` queue — see R9):
1. **Transcribe** the media file (whisper.cpp, local) → timestamped segments. Store the transcript on the media file (R8) so re-proposals skip re-transcription.
2. **Segment** the transcript (LLM, OpenAI-compatible, provider-configurable — R7) → proposed `{start_time, title}` chapters, sermon-structure-aware (scripture, prayer, points, application, benediction), ≤20, first at 0.
3. **Sanitize** server-side: cap 20, clamp times to `[0, duration)`, sort, dedupe, drop blanks. Store as the **proposal** (R8) — NOT in the `chapters` table.
4. The editor loads the proposal as editable drafts; the user reviews and **saves** via the normal sync endpoint (FR-007), which is the only thing that publishes chapters to RSS.

**Why not even-split**: the user's content is mostly **sermons**, where content-aligned boundaries matter; an even split would put chapter edges mid-sentence/mid-thought. Content-aware is the explicit ask.

**Alternatives**:
- Even-split (old plan): cheap but not content-aware. Rejected per user.
- Silence-detection (ffmpeg): better than even-split but pauses ≠ topics in a sermon. Rejected for LLM segmentation.

## R4 — Validation rules (server-side, `ChapterSyncRequest`)

**Decision**: Whole-array replacement sync (like feed items). Per item: `title` required string 1–255; `start_time` required integer `≥ 0` and **`< mediaFile.duration`** (duration resolved server-side); unique `start_time` per file; whole array `max:20`. Authorization via policy before side effects (`media_file.user_id === Auth::id`).

Server-side enforcement is mandatory because the RSS output and external consumers must never see invalid chapters (also covers LLM-produced proposals after the user hits Save). Replacement-sync mirrors `FeedController::syncFeedItems`.

**Alternatives**: per-chapter CRUD endpoints (harder 20-cap, more races). Rejected.

## R5 — Routing & API-first compliance

**Decision**: Two web/Inertia routes under the existing `auth`/`verified`/`approved` group:
- `PUT  /library/{library_item}/chapters`         → `ChapterController@sync` (save/replace).
- `POST /library/{library_item}/chapters/generate` → `ChapterController@generate` (kick off proposal).

Both mirror how `LibraryController` works; business logic (validation, cap, dispatch, cache invalidation) is server-side, satisfying the constitution's API-first intent (backend contract before/with frontend; Inertia is the transport), exactly as feeds/library already comply. A future `Api/V1` chapter surface is a one-line addition if external consumers need it (YAGNI now).

**Alternatives**: extend `LibraryController@update` with a `chapters` key (couples concerns). Build Api/V1 first (no external consumer yet). Both rejected.

## R6 — RSS cache invalidation

**Decision**: On chapter sync, clear `rss.{feed_id}` for every feed containing any library item using the edited media file — reusing `LibraryController`'s `Cache::forget("rss.{$feedId}")` loop.

**Rationale**: RSS is `Cache::remember`'d; without invalidation, saved chapters stay stale until TTL. One media file may appear in several feeds, so flush all.

## R7 — Language-model client (provider-configurable)

**Decision**: A small `LlmClient` service wrapping an **OpenAI-compatible** `/chat/completions` call (JSON response mode), config-driven: `config('services.llm.base_url')`, `services.llm.api_key`, `services.llm.model`. The user's **z.ai** key works via z.ai's OpenAI-compatible endpoint; switching providers is an `.env` change only (no code) — satisfying the user's "control to switch providers" requirement.

**Prompt contract**: send the transcript segments (text + start times) and instruct the model to return a JSON array of `[{start, title}]` (≤20, content-aligned, first at 0, sermon-aware). Parse defensively (LLMs sometimes wrap JSON in markdown prose — extract the JSON).

**Alternatives**: hardcode z.ai (no swap control) — rejected. A local LLM (Ollama) — heavier ops, deferred (the user has a cloud key and accepts the cost).

## R8 — Where generation state & transcript live (data model)

**Decision**: Store proposal state and the transcript as **nullable columns on `media_files`** (cheapest, no joins):

| Column | Type | Purpose |
|---|---|---|
| `transcript` | json, nullable | whisper.cpp segment output (text + start/end). Persisted so re-proposals skip re-transcription (the expensive local step). |
| `chapter_generation_status` | enum(`pending`,`processing`,`completed`,`failed`), nullable | Drives the UI's progress/polling + retry. |
| `chapter_proposal` | json, nullable | The LLM's proposed `{start_time,title}[]` held for user review. **Never** read by RSS. |
| `chapter_generation_error` | text, nullable | Failure detail surfaced to the user. |

**Why a holding area (not direct writes to `chapters`)**: AI-generated chapters must **not** be published to the RSS feed unreviewed. The proposal lives in `chapter_proposal` until the user saves, at which point it goes through the normal sync → `chapters` table → RSS.

**Alternatives**: a separate `chapter_proposals` table — more joins for transient state. Rejected for simplicity. Not storing the transcript — would force re-transcription on every re-proposal (wasteful for long sermons). Rejected.

**Staleness**: if the media file is re-downloaded/replaced (different audio), the cached `transcript` and `chapter_proposal` belong to the old audio and MUST be cleared (alongside the existing duration-change handling in FR-011) so the next proposal re-transcribes the new audio.

## R9 — whisper.cpp transcription + the dedicated low-priority queue

**Decision (two parts):**

**Engine**: **whisper.cpp** (self-contained C++ binary + a ggml model) for transcription, added to the production image alongside yt-dlp/ffmpeg. Default model **`small.en`** (~470 MB, good English accuracy/speed for sermons); model path configurable. Transcription shells out to the binary, writing timestamped JSON/SRT the job parses into `transcript`. **Video media** (required by FR-008/SC-007): whisper.cpp ingests audio only, so the job first extracts a 16 kHz mono WAV via **ffmpeg** (already in the image) and transcribes that.

**Queue isolation (per user)**: the transcription + segmentation jobs dispatch with `->onQueue('chapters')` — a **separate, low-priority queue** distinct from the `default` queue that handles uploads/downloads/feed jobs. The database queue driver has no native priority weighting, so "low priority" = a **dedicated worker process for the `chapters` queue** (single concurrency, optionally OS-`nice`d so the CPU-bound whisper.cpp subprocess can't starve the server). This guarantees a long sermon transcription never blocks routine operations (SC-008).

**Job shape**: two small queued jobs chained on `chapters`:
- `TranscribeMediaFile` (expensive, CPU) — produces/stores `transcript` if missing.
- `SegmentTranscriptIntoChapters` (quick LLM call) — produces/stores `chapter_proposal`, sets status.
Chaining means a segmentation failure doesn't force a re-transcription.

**Deployment note (user-driven, per AGENTS.md)**: the production image must include whisper.cpp + model, and the production worker pool must run a `chapters`-queue worker. The app code dispatches to `chapters`; the user rebuilds/redeploys. I will not modify Dockerfiles or run production workers.

**Alternatives**:
- OpenAI Whisper cloud API (audio leaves server, paid per use) — rejected; user chose local transcription.
- openai-whisper Python (PyTorch) — far heavier image than whisper.cpp. Rejected.
- Run on the `default` queue — would let transcription block uploads/downloads. Rejected per user.
- Redis/Horizon weighted priorities — major infra change vs. the constitution's database queue. Rejected.

## R10 — Where the chapter editor lives (UI)

**Decision**: A "Chapters" section inside the existing **"Edit Media" sheet** on the dashboard Library tab (`dashboard.tsx`), rendered by a new `chapter-editor.tsx`. Shown only when `media_file.duration` exists (processing complete). It hosts both manual authoring (Story 1) and the **"Generate from content"** action (Story 2): clicking it `POST`s to the generate route, then the sheet **polls** `chapter_generation_status` (reusing the dashboard's existing 5s reload pattern) until `completed`/`failed`, then loads `chapter_proposal` as editable drafts. The add-media sheet does not offer chapters (no duration yet during processing).

**Alternatives**: chapters on the feed edit page per item (clutters feed composition); a dedicated chapters page (over-scoped). Both rejected.

## R11 — P3 in-app player chapter display

**Decision (P3, deferrable)**: Add a seekable chapter list to `media-player.tsx` (chapters via the library item's media file); on click set `audio|video.currentTime` to `start_time` (needs a ref on the media element). Coherent enhancement; the feed publication (Story 1) already delivers primary value to listeners.

---

**Resolved NEEDS CLARIFICATION count**: 0. Spec assumption refined (R1: chapters on `MediaFile`). Two user-confirmed decisions folded in (R3 content-aware proposal; R9 low-priority `chapters` queue). Ready for Phase 1.
