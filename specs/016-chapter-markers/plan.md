# Implementation Plan: Media Chapter Markers

**Branch**: `016-chapter-markers` | **Date**: 2026-07-24 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/016-chapter-markers/spec.md`

## Summary

Author up to 20 timestamped chapters (start time + title) on a media item (P1), available once processed (duration known) and editable any time, published into the RSS feed via **Podlove Simple Chapters**. **Content-aware automatic proposals (P2)** transcribe the audio on the server (**whisper.cpp**) and segment the transcript via an **OpenAI-compatible LLM** (provider-configurable — z.ai today) to produce ≤20 sermon-structure chapters as editable drafts, run as chained jobs on a **dedicated low-priority `chapters` queue** so heavy transcription never blocks routine operations. P3 surfaces chapters in PodKeep's own players. Chapters attach to **MediaFile** (shared by duplicate items).

## Technical Context

**Language/Version**: PHP 8.4 (Laravel 13), TypeScript (React 19+)
**Primary Dependencies**: Laravel 13, Inertia.js v3, Tailwind CSS v4, Pest PHP v4, lucide-react; existing `rss.blade.php` + DOMDocument XML validation; **whisper.cpp** (new, transcription); OpenAI-compatible LLM via `Http` (new)
**Storage**: PostgreSQL (production) / SQLite (tests); new `chapters` table + nullable generation-state columns on `media_files` (`transcript`, `chapter_generation_status`, `chapter_proposal`, `chapter_generation_error`); local `public` disk for media (unchanged)
**Testing**: Pest feature tests (backend); TDD per constitution — sync/cap/validation, RSS output, ownership, **generation status transitions, proposal sanitization, queue dispatch**
**Target Platform**: Web application (Docker)
**Project Type**: Laravel + Inertia/React with a parallel Sanctum Api/V1 surface
**Performance Goals**: chapter sync <500ms; RSS feed generation <5s (chapters add O(n) small elements); transcription async on `chapters` queue (never blocks `default`); RSS cache invalidated on save
**Constraints**: Follow existing `LibraryController`/feed-item sync patterns; RSS output stays valid XML (`RssController` re-validates); 20-chapter hard cap; LLM provider must be env-switchable; heavy work isolated on `chapters` queue
**Scale/Scope**: User-scoped; ≤20 chapters/file; transcription is the only heavy new work (CPU, minutes-long)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **API-First**: Backend lands first — migrations → `Chapter` model → `ChapterSyncRequest` → `ChapterController` (sync + generate) → jobs → `LlmClient` → RSS output. The React editor consumes those server contracts. Mirrors how feeds/library already satisfy API-first (server-side controller+request+model is the contract; Inertia is the transport). A parallel Sanctum `Api/V1` chapter surface is a documented follow-up, not required for the in-app feature.
- [x] **Media Processing**: APPLIES (no longer N/A). Transcription + segmentation run **asynchronously through queued jobs** on a dedicated queue, with status tracking (`pending→processing→completed|failed`) and user-visible retry on failure — no destructive media mutation (transcript is additive). This satisfies the async/resilient-processing principle without touching the existing upload pipeline.
- [x] **Test-Driven**: Pest tests first — chapter sync (full replace), 20-cap, start-time-within-duration validation, RSS output includes/omits chapters, ownership; plus generate-route dispatches to the `chapters` queue, status transitions, and proposal sanitization (cap/clamp/sort/dedupe). The LLM and whisper.cpp calls are faked/stubbed in tests.
- [x] **Feed Standards**: Chapters in `rss.blade.php` via the **Podlove Simple Chapters** namespace (`psc:chapters`, inline). `RssController` re-validates generated XML via `DOMDocument` and throws on malformation — chapters can't break feed validity.
- [x] **Security**: `ChapterSyncRequest` validates (cap 20, `start_time ≥ 0` and `< duration`, unique times, non-empty titles ≤255). `ChapterPolicy`/Gate enforces ownership (`media_file.user_id === Auth::id`) for both sync and generate. LLM key stored in `.env`/config, never exposed to the client. User-scoped queries only.
- [x] **Performance**: RSS cached by `rss.{feed_id}`; chapter save clears it for every affected feed (existing pattern). Transcription/segmentation isolated on the `chapters` queue so it can't starve the `default` queue (SC-008). Chapters eager-loaded only on the edit view and RSS render.

No violations. No complexity tracking entries required.

## Project Structure

### Documentation (this feature)

```text
specs/016-chapter-markers/
├── plan.md, research.md, data-model.md, quickstart.md
├── contracts/{chapter-data-contract.md, rss-chapter-format.md,
│              chapter-editor-payload.md, chapter-generation-pipeline.md}
└── tasks.md   # (/speckit.tasks — not created here)
```

### Source Code (repository root)

```text
src/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── ChapterController.php          # NEW — sync() + generate()
│   │   └── Requests/
│   │       └── ChapterSyncRequest.php         # NEW — cap/time/title validation
│   ├── Jobs/
│   │   ├── TranscribeMediaFile.php            # NEW — whisper.cpp → transcript (chapters queue)
│   │   └── SegmentTranscriptIntoChapters.php  # NEW — LLM → chapter_proposal (chapters queue)
│   ├── Models/
│   │   ├── Chapter.php                        # NEW — belongsTo MediaFile
│   │   └── MediaFile.php                      # hasMany chapters; + generation-state casts
│   ├── Services/
│   │   ├── LlmClient.php                      # NEW — OpenAI-compatible, config-driven
│   │   └── Transcription/WhisperClient.php    # NEW — shells out to whisper.cpp
│   └── Policies/
│       └── ChapterPolicy.php                  # NEW — owner-only
├── config/
│   └── services.php                           # llm => [base_url, api_key, model] (env-driven)
├── database/
│   ├── migrations/
│   │   ├── 2026_07_24_..._create_chapters_table.php            # NEW
│   │   └── 2026_07_24_..._add_chapter_generation_to_media_files.php  # NEW
│   └── factories/ChapterFactory.php           # NEW
├── resources/
│   ├── views/rss.blade.php                    # xmlns:psc + per-item <psc:chapters>
│   └── js/
│       ├── components/
│       │   ├── chapter-editor.tsx             # NEW — list + generate + add/edit/remove + poll
│       │   └── media-player.tsx               # P3 — seekable chapter list
│       ├── pages/dashboard.tsx                # "Edit Media" sheet gains a Chapters section
│       └── types/index.d.ts                   # Chapter + MediaFile generation fields
└── tests/Feature/ChapterManagementTest.php    # NEW — sync, cap, validation, RSS, ownership, generate/status
```

**Structure Decision**: Pure addition. One new table + nullable media-file generation columns; one model/policy/request; one web controller (sync + generate); two queued jobs + two thin service clients (whisper.cpp, LLM) on the `chapters` queue; a chapter-editor component; small RSS + dashboard edits. Chapters + transcripts live on `media_files` (shared by duplicate items). **Deployment prerequisites (user-driven)**: production image must include whisper.cpp + model, and run a `chapters`-queue worker; `.env` must set `LLM_*`. I will not modify Dockerfiles or production workers (AGENTS.md).
