---

description: "Task list for feature 016-chapter-markers"
---

# Tasks: Media Chapter Markers

**Input**: Design documents from `/specs/016-chapter-markers/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: MANDATORY per constitution (TDD, red-green). The project has no JS test runner, so frontend behavior is covered by Pest feature tests (asserting Inertia props / RSS / DB / queue) where a backend surface exists, plus the manual verification in `quickstart.md`. The whisper.cpp and LLM calls are **faked/stubbed** in tests via injectable service clients.

**Organization**: Tasks grouped by user story. **US1** (author + publish) is the foundation — it owns the `chapters` table/model/sync/RSS. **US2** (content-aware generation) builds on US1 (proposal → user saves via US1's sync). **US3** (in-app players) builds on US1 (reads chapters). US2 and US3 are independent of each other.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependency on an incomplete task)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)
- Exact file paths included in every description

## Path Conventions

- **Backend**: `src/app/Http/Controllers/`, `src/app/Http/Requests/`, `src/app/Models/`, `src/app/Jobs/`, `src/app/Services/`, `src/app/Policies/`
- **Frontend**: `src/resources/js/components/`, `src/resources/js/pages/`, `src/resources/js/types/`
- **DB / Config / Views**: `src/database/migrations/`, `src/database/factories/`, `src/config/`, `src/resources/views/`
- **Tests**: `src/tests/Feature/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Confirm the working environment before any code change.

- [X] T001 Verify branch `016-chapter-markers` is checked out and `src/vendor/` is in sync with `src/composer.json` (run the ephemeral `composer install` from `AGENTS.md` if stale). No new Composer/NPM dependency is required (whisper.cpp is a binary added to the image; the LLM uses Laravel's `Http` facade). No code change — environment readiness only.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared infrastructure that MUST be complete before ANY user story.

**No foundational tasks required.** The `chapters` entity is the core of US1 (earliest story), so it lives in Phase 3. US2 (generation) and US3 (players) each build on US1 and on no other shared infrastructure:
- US2 adds `media_files` generation columns + jobs + an LLM/whisper client; it depends on US1's editor + sync (to save proposals).
- US3 adds player UI; it depends on US1's chapters existing.
- Deployment prerequisites (whisper.cpp + model, a `chapters`-queue worker, `LLM_*` env) are user-driven and documented in `quickstart.md`; they are not code tasks here.

**Checkpoint**: Foundation ready — user story implementation can begin (US1 first; US2/US3 after US1).

---

## Phase 3: User Story 1 - Author & Publish Chapters (Priority: P1) MVP

**Goal**: A user can add/edit/re-time/remove up to 20 chapters (start time + title) on a processed media item from the "Edit Media" sheet, and those chapters are published into the RSS feed via Podlove Simple Chapters (`psc:chapters`). Chapters attach to `MediaFile` (shared by duplicate items).

**Independent Test**: Add a processed media item, create chapters with valid start times/titles, confirm the 20-cap and duration validation are enforced, and confirm the item's RSS feed contains `<psc:chapters>` — without using automatic generation or any in-app player.

### Tests for User Story 1 (write first, confirm RED)

- [X] T002 [P] [US1] Add feature tests in `src/tests/Feature/ChapterManagementTest.php`: `PUT /library/{library_item}/chapters` (a) replaces the full set and deletes chapters not in the payload, (b) rejects a 21st chapter (20-cap), (c) allows `start_time` 0 and rejects `start_time ≥ duration`, (d) rejects duplicate `start_time`, (e) rejects blank titles, (f) returns 403 for non-owners.
- [X] T003 [P] [US1] Add feature tests in `src/tests/Feature/ChapterRssTest.php`: a feed item whose media file has chapters emits `<psc:chapters>` with `start` formatted as `H:MM:SS`; an item with no chapters emits none; the feed remains valid RSS/XML; and saving chapters clears the `rss.{feed_id}` cache (feed reflects changes without waiting for TTL).

### Implementation for User Story 1

- [X] T004 [US1] Create migration `src/database/migrations/2026_07_24_hhmmss_create_chapters_table.php`: `media_file_id` foreignId (cascade on delete), `start_time` integer, `title` string(255), timestamps; `unique(['media_file_id','start_time'])` and `index('media_file_id')`.
- [X] T005 [P] [US1] Create `src/app/Models/Chapter.php` (`belongsTo MediaFile`) and `src/database/factories/ChapterFactory.php` (valid `media_file_id`, `start_time`, `title`).
- [X] T006 [P] [US1] In `src/app/Models/MediaFile.php` add `chapters(): hasMany` returning `$this->hasMany(Chapter::class)->orderBy('start_time')`.
- [X] T007 [P] [US1] Create `src/app/Policies/ChapterPolicy.php` authorizing the authenticated user only when they own the parent media file (`$libraryItem->mediaFile->user_id === Auth::id()`); register/wire it so `ChapterController` can authorize.
- [X] T008 [P] [US1] Create `src/app/Http/Requests/ChapterSyncRequest.php`: `chapters` array `max:20`; each `start_time` required integer `min:0` and **`< media_file.duration`** (resolve duration server-side from the route-bound item's media file); `title` required string `min:1 max:255`; reject duplicate `start_time` within the payload.
- [X] T009 [US1] Create `src/app/Http/Controllers/ChapterController.php` with `sync()`: authorize via policy, validate via `ChapterSyncRequest`, replace the media file's chapters (delete missing, upsert submitted), then `Cache::forget("rss.{$feedId}")` for every feed containing any library item that uses this media file (mirror `LibraryController`), and redirect back with success. (Depends T005–T008.)
- [X] T010 [US1] In `src/routes/web.php` add `Route::put('library/{library_item}/chapters', [ChapterController::class, 'sync'])->name('library.chapters.sync')` inside the `auth`/`verified`/`approved` group.
- [X] T011 [US1] RSS rendering: in `src/resources/views/rss.blade.php` add `xmlns:psc="http://podlove.org/simple-chapters"` to `<rss>` and, inside each `<item>`, emit `<psc:chapters version="1.2">` with one `<psc:chapter start="H:MM:SS" title="…"/>` per chapter (ordered, only when ≥1 chapter); and in `src/app/Http/Controllers/RssController.php` add `items.libraryItem.mediaFile.chapters` to the eager-load list.
- [X] T012 [P] [US1] In `src/resources/js/types/index.d.ts` add the `Chapter` interface (`id, media_file_id, start_time, title, created_at, updated_at`) and `chapters?: Chapter[]` on `MediaFile`.
- [X] T013 [US1] Create `src/resources/js/components/chapter-editor.tsx`: manual authoring — list of `{start_time,title}` rows (seeded from `media_file.chapters`), add/remove/rename/re-time inline, sorted display, "Save" posts the full array to the sync route (Inertia), hard-disable Add at 20 rows. Render only when `media_file.duration` exists. (Depends T012.)
- [X] T014 [US1] In `src/routes/web.php` update the dashboard `libraryItems` query to eager-load `mediaFile.chapters` (so the editor opens with existing chapters without an extra request).
- [X] T015 [US1] In `src/resources/js/pages/dashboard.tsx` add a "Chapters" section to the "Edit Media" `SheetPanel` that renders `<ChapterEditor>` for the selected library item. (Depends T013, T014.)

**Checkpoint**: User Story 1 fully functional and independently testable. This is the MVP — stop and validate here if desired.

---

## Phase 4: User Story 2 - Content-Aware Chapter Generation (Priority: P2)

**Goal**: A user clicks "Generate chapters from content"; the system transcribes the audio locally (whisper.cpp) and segments the transcript via an OpenAI-compatible LLM (z.ai today, env-switchable), producing ≤20 sermon-structure chapter drafts the user reviews and saves via US1's sync. Runs as chained jobs on a dedicated low-priority `chapters` queue. Proposals are never auto-published.

**Independent Test**: On a long processed item, request generation; assert the job chain dispatches on the `chapters` queue, status moves `pending→processing→completed`, the proposal is sanitized (≤20, clamped, sorted, deduped) and held in `chapter_proposal` (NOT in `chapters`), and re-generation reuses the cached transcript.

### Tests for User Story 2 (write first, confirm RED)

- [X] T016 [P] [US2] Add feature tests in `src/tests/Feature/ChapterGenerationTest.php`: `POST /library/{library_item}/chapters/generate` (a) sets `media_file.chapter_generation_status = 'pending'` and dispatches the job chain on the **`chapters`** queue (`Queue::fake`, assert `onQueue('chapters')`), (b) rejects when the media file has no duration, (c) returns 403 for non-owners.
- [X] T017 [P] [US2] Add feature tests in `src/tests/Feature/ChapterGenerationJobTest.php` (WhisperClient + LlmClient **faked**): `TranscribeMediaFile` stores `transcript` and sets `processing`; `SegmentTranscriptIntoChapters` calls the LLM, sanitizes the proposal (cap 20, clamp to `[0,duration)`, sort, dedupe, strip blanks), stores `chapter_proposal`, sets `completed`, and does **not** write the `chapters` table; on failure sets `failed` + `chapter_generation_error`; re-running the chain reuses an existing `transcript` (transcription skipped).

### Implementation for User Story 2

- [X] T018 [US2] Create migration `src/database/migrations/2026_07_24_hhmmss_add_chapter_generation_to_media_files.php`: nullable `transcript` json, `chapter_generation_status` string(16), `chapter_proposal` json, `chapter_generation_error` text (after `duration`).
- [X] T019 [P] [US2] In `src/app/Models/MediaFile.php` add the generation columns to `fillable`/`casts` (status as enum/string), and in `src/resources/js/types/index.d.ts` add `transcript`, `chapter_generation_status`, `chapter_proposal`, `chapter_generation_error` to `MediaFile`.
- [X] T020 [P] [US2] In `src/config/services.php` add `'llm' => ['base_url'=>env('LLM_BASE_URL'), 'api_key'=>env('LLM_API_KEY'), 'model'=>env('LLM_MODEL')]`, and add `LLM_BASE_URL`/`LLM_API_KEY`/`LLM_MODEL` (+ optional `WHISPER_MODEL_PATH`) placeholders to `src/.env.example`.
- [X] T021 [P] [US2] Create `src/app/Services/LlmClient.php`: OpenAI-compatible `chat/completions` call (JSON response mode) against `services.llm.base_url` with the configured key/model; defensively extract JSON from the reply. Injectable so tests can swap it.
- [X] T022 [P] [US2] Create `src/app/Services/Transcription/WhisperClient.php`: extract a 16 kHz mono WAV via **ffmpeg** for video media, shell out to **whisper.cpp** (model path from config), parse timestamped output to `{start,end,text}[]`. Injectable so tests can swap it.
- [X] T023 [US2] Create `src/app/Jobs/TranscribeMediaFile.php` (`onQueue('chapters')`): if `media_file.transcript` is missing, call `WhisperClient`, store `transcript`, set `chapter_generation_status='processing'`; on exception set `failed` + error. (Depends T018, T019, T022.)
- [X] T024 [US2] Create `src/app/Jobs/SegmentTranscriptIntoChapters.php` (`onQueue('chapters')`): call `LlmClient` with the transcript (prompt: ≤20 sermon-structure chapters, first at 0, JSON `[{start,title}]`), sanitize server-side (cap 20, clamp `[0,duration)`, sort, dedupe, strip blanks), store `chapter_proposal`, set `completed` (or `failed`+error). (Depends T023.)
- [X] T025 [US2] Add `ChapterController@generate()` (authorize, require duration, set `pending`, dispatch `TranscribeMediaFile::withChain([new SegmentTranscriptIntoChapters($mediaFile)])->onQueue('chapters')`) and add `Route::post('library/{library_item}/chapters/generate', [ChapterController::class,'generate'])->name('library.chapters.generate')` in `src/routes/web.php`. (Depends T023, T024.)
- [X] T026 [US2] In `src/resources/js/components/chapter-editor.tsx` add a "Generate chapters from content" button: `POST` to the generate route, then poll `media_file.chapter_generation_status` (reuse the dashboard's ~5s reload pattern) showing progress; on `completed` load `media_file.chapter_proposal` as editable drafts; on `failed` show `chapter_generation_error` with a retry; disable while running. (Extends T013; depends T019.)
- [X] T027 [P] [US2] In the re-download path (`src/app/Http/Controllers/LibraryController.php` and/or the `RedownloadMediaFile` job) clear `media_file.transcript`, `chapter_proposal`, and `chapter_generation_status` when the media is re-downloaded/replaced, so the next proposal re-transcribes the new audio (FR-011 / stale-cache fix).

**Checkpoint**: User Stories 1 AND 2 both functional. Automatic proposals produce content-aligned, reviewable chapters without blocking the main queue.

---

## Phase 5: User Story 3 - Chapters in PodKeep's Own Players (Priority: P3, deferrable)

**Goal**: Chapters are shown as a seekable list in PodKeep's own players (dashboard + public share page); selecting a chapter seeks to its start time.

**Independent Test**: Play a chaptered item in the dashboard player and the share-page player; confirm the chapter list renders and selecting a chapter seeks playback to that start time — without re-authoring chapters.

### Implementation for User Story 3

- [X] T028 [P] [US3] In `src/resources/js/components/media-player.tsx` add a ref to the `<audio>`/`<video>` element and render a seekable chapter list (from `libraryItem.media_file.chapters`); on chapter click set `mediaEl.currentTime = start_time`.
- [X] T029 [P] [US3] In `src/resources/js/components/share-player.tsx` add a seekable chapter list (the `audioRef` already exists); plumb chapters through `src/app/Http/Controllers/ShareController.php` (include each episode's chapters from its media file) and add `chapters?: Chapter[]` to the `ShareEpisode` type in `src/resources/js/types/index.d.ts`.

**Checkpoint**: Chapters surface everywhere PodKeep plays media (feed + both in-app players).

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Quality gates mandated by `AGENTS.md`, run after code is complete.

- [X] T030 [P] Run PHPStan: `docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html --entrypoint vendor/bin/phpstan podkeep-app:latest analyse --no-progress`; resolve all findings.
- [X] T031 [P] Run Pint on changed PHP: `docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html --entrypoint vendor/bin/pint podkeep-app:latest --dirty`.
- [X] T032 [P] Run fallow on changed JS/TS: `docker run --rm -v /home/nate/src/podkeep:/repo -w /repo/src --entrypoint sh node:22 -c 'git config --global --add safe.directory /repo && ./node_modules/.bin/fallow audit --base main'`; resolve findings.
- [X] T033 Run the affected Pest suites — `ChapterManagementTest`, `ChapterRssTest`, `ChapterGenerationTest`, `ChapterGenerationJobTest`, `RssFeedTest`, `FeedEditTest`, `LibraryUploadTest` — via the ephemeral `docker run --rm ... artisan test` command from `AGENTS.md`; complete the manual verification steps in `src/specs/016-chapter-markers/quickstart.md`; and confirm the deployment prerequisites are documented (production image needs whisper.cpp + model and a `chapters`-queue worker; `.env` needs `LLM_*`).

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately.
- **Foundational (Phase 2)**: No tasks; independence documented. Blocks nothing.
- **US1 (Phase 3)**: The foundation. Start after Setup.
- **US2 (Phase 4)**: Depends on **US1** (uses the editor + the sync endpoint to save proposals; chapters table must exist). Does not depend on US3.
- **US3 (Phase 5)**: Depends on **US1** (chapters must exist + be eager-loaded). Does not depend on US2. Deferrable.
- **Polish (Phase 6)**: After the desired stories are complete.

### User Story Dependencies

- **US1**: T002/T003 (red) → T004 (migration) → T005/T006/T007/T008 (parallel: model, relation, policy, request) → T009 (controller) + T010 (route) → T011 (RSS) → T012 (type) → T013 (editor) → T014 (eager-load) → T015 (dashboard integration).
- **US2**: T016/T017 (red) → T018 (migration) → T019/T020/T021/T022 (parallel: model+type, config, LlmClient, WhisperClient) → T023 (transcribe job) → T024 (segment job) → T025 (controller+route) → T026 (editor "Generate" + poll). T027 (redownload staleness) is independent.
- **US3**: T028 and T029 are independent files → parallel. Both depend only on US1 being done.

### Within Each User Story

- Write tests first; confirm they FAIL before implementing.
- Migration → model → request → controller → route (backend) before frontend that consumes it.
- The whisper.cpp/LLM clients are injectable so job tests fake them (no real transcription in CI).

### Parallel Opportunities

- T002 & T003 (US1 tests, different files); T016 & T017 (US2 tests, different files) — parallel.
- T005/T006/T007/T008 (US1 model/relation/policy/request) — different files, parallel.
- T019/T020/T021/T022 (US2 model+type/config/LlmClient/WhisperClient) — different files, parallel.
- T027 (US2 redownload) is independent of the US2 job chain.
- T028 & T029 (US3 players) — different files, parallel.
- Polish T030/T031/T032 — different tools, parallel (after all code lands).
- US2 and US3 can be developed in parallel by different developers once US1 is complete.

---

## Parallel Example: User Story 2

```bash
# Red tests together (different files):
Task T016: "US2 generate-route tests in src/tests/Feature/ChapterGenerationTest.php"
Task T017: "US2 job tests in src/tests/Feature/ChapterGenerationJobTest.php"

# After migration T018, these independent files together:
Task T019: "US2 generation columns on src/app/Models/MediaFile.php + types"
Task T020: "US2 llm config in src/config/services.php + .env.example"
Task T021: "US2 LlmClient in src/app/Services/LlmClient.php"
Task T022: "US2 WhisperClient in src/app/Services/Transcription/WhisperClient.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 only)

1. T001 — verify environment.
2. T002–T015 — author + publish chapters (manual authoring + RSS).
3. **STOP and VALIDATE**: create chapters, confirm the 20-cap/duration validation, and confirm the RSS feed contains `<psc:chapters>`.
4. Commit; deploy/demo if desired. Manual chapters deliver the core value immediately.

### Incremental Delivery

1. Setup → US1 (MVP: author + publish) → validate → commit.
2. US2 (content-aware generation) → validate → commit. *(Requires deployment: whisper.cpp + `chapters` worker + `LLM_*` env.)*
3. US3 (in-app players) → validate → commit. (Deferrable.)
4. Polish: PHPStan + Pint + fallow + full affected Pest suite.

### Parallel Team Strategy

- Developer A: US1 (the foundation).
- Once US1 lands: Developer B → US2 (jobs/LLM/whisper pipeline); Developer C → US3 (players). US2 and US3 do not conflict.

---

## Notes

- [P] tasks = different files, no write-time dependency on an incomplete task.
- [Story] label maps a task to its user story for traceability.
- Each user story is independently completable and testable.
- Confirm tests fail before implementing; commit after each task or logical group.
- Stop at any checkpoint to validate a story independently.
- Avoid: vague tasks, same-file conflicts (e.g., T013 & T026 both edit `chapter-editor.tsx` — run sequentially; T019 & T006 both edit `MediaFile.php` across phases — sequence US1 before US2), cross-story dependencies that break independence.
- whisper.cpp and the LLM are **deployment/external** dependencies — app code dispatches to the `chapters` queue and calls injectable clients; the user adds whisper.cpp + the worker + `LLM_*` env (per `AGENTS.md`, do not modify Dockerfiles/production workers).
