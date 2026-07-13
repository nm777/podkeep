# Tasks: Video Podcast Support

**Input**: Design documents from `/specs/014-video-podcast-support/`  
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, quickstart.md

**Tests**: Feature tests for video processing and RSS enclosures.

**Organization**: Tasks grouped by user story for independent implementation.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `src/app/Enums/`, `src/app/Models/`, `src/app/Services/`, `src/app/Http/`
- **Frontend**: `src/resources/js/components/`, `src/resources/js/types/`
- **Migrations**: `src/database/migrations/`
- **Tests**: `src/tests/Feature/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create the enum and migration all user stories depend on

- [ ] T001 Create `MediaType` enum with `Audio = 'audio'` and `Video = 'video'` cases, including `isAudio()` and `isVideo()` helper methods, in `src/app/Enums/MediaType.php`
- [ ] T002 Create migration to add nullable `media_type` string column (default `audio`) to `library_items` table, in `src/database/migrations/2026_07_12_000002_add_media_type_to_library_items.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Update models, validation, and services that all user stories share

- [ ] T003 [P] Update LibraryItem model: add `media_type` to fillable, add `'media_type' => MediaType::class` to casts, add `$attributes['media_type' => 'audio']` default, in `src/app/Models/LibraryItem.php`
- [ ] T004 [P] Update TypeScript types: add `media_type: 'audio' | 'video'` to LibraryItem interface in `src/resources/js/types/index.d.ts`
- [ ] T005 [P] Update MediaValidator: extend `validateMediaContent()` to accept video signatures (EBML/WebM `\x1A\x45\xDF\xA3`, AVI RIFF+AVI), update `detectMimeTypeFromContent()` to return video MIME types for video files, in `src/app/Services/MediaProcessing/MediaValidator.php`
- [ ] T006 [P] Update MediaDownloader: extend `validateMediaContent()` method to accept video signatures alongside audio, in `src/app/Services/MediaProcessing/MediaDownloader.php`
- [ ] T007 [P] Create VideoToAudioConverter service: wraps ffmpeg via Symfony Process to extract audio from video file (`ffmpeg -i input -vn -acodec libmp3lame -q:a 0 output.mp3`), in `src/app/Services/MediaProcessing/VideoToAudioConverter.php`
- [ ] T008 [P] Update StoreLibraryRequest: add `'media_type' => ['nullable', 'string', Rule::enum(MediaType::class)]` validation rule, in `src/app/Http/Requests/StoreLibraryRequest.php`

**Checkpoint**: Foundation ready — enum, migration, models, validator, converter, request validation all updated.

---

## Phase 3: User Story 1 — Choose Media Type When Adding Content (Priority: P1) 🎯 MVP

**Goal**: Users choose "Audio only" or "Keep video" when uploading, importing from URL, or importing from YouTube. Video sources can be converted to audio via ffmpeg.

**Independent Test**: Upload an MP4 and choose "Audio only" → verify audio file is produced. Upload same MP4 and choose "Keep video" → verify video file stored as-is.

- [ ] T009 [US1] Update MediaProcessingService `processFromUrl()`: accept `media_type` parameter, when source is video and user chose audio, call VideoToAudioConverter after download, in `src/app/Services/MediaProcessing/MediaProcessingService.php`
- [ ] T010 [US1] Update YouTubeDownloader: add `downloadVideo()` method that omits `--extract-audio` and uses `--format 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4'` for video, keep existing `downloadAudio()` for audio, in `src/app/Services/YouTube/YouTubeDownloader.php`
- [ ] T011 [US1] Update YouTubeProcessingService `processYouTubeUrl()`: accept `media_type` parameter, call `downloadVideo()` when video requested, `downloadAudio()` when audio requested, in `src/app/Services/YouTube/YouTubeProcessingService.php`
- [ ] T012 [US1] Update LibraryController `store()`: pass `media_type` from validated request to the processing job/service, in `src/app/Http/Controllers/LibraryController.php`
- [ ] T013 [US1] Add media type selector (radio or toggle: "Audio only" / "Keep video") to the upload form, only showing "Keep video" when the source could be video, in `src/resources/js/components/media-upload-button.tsx`

**Checkpoint**: Users can choose media type at creation. Video sources can be converted to audio.

---

## Phase 4: User Story 2 — Video Files in RSS Feeds (Priority: P2)

**Goal**: Video items appear in RSS feeds with correct video MIME types in enclosure tags.

**Independent Test**: Add a video item to a feed. Fetch RSS XML. Verify enclosure has `type="video/mp4"`.

- [ ] T014 [US2] Feature test: create a library item with media_type=video and a media file with video/mp4 mime type, add to a public feed, fetch RSS, assert enclosure type is `video/mp4`. Also test audio item still gets `audio/mpeg`. Test mixed feed, in `src/tests/Feature/VideoPodcastTest.php`

**Note**: No code changes needed for RSS — the template already reads `mime_type` from MediaFile. This task is a verification test.

**Checkpoint**: Video items produce correct RSS enclosures automatically.

---

## Phase 5: User Story 3 — Video Playback in the App (Priority: P3)

**Goal**: Video items use a video player; audio items use the existing audio player.

**Independent Test**: Click play on a video item → video player appears. Click play on audio item → audio player as before.

- [ ] T015 [US3] Update MediaPlayer component: when `item.media_type === 'video'` (or mime_type starts with `video/`), render a `<video>` element with controls instead of the `<audio>` element, keeping the same dialog/sheet layout, in `src/resources/js/components/media-player.tsx`

**Checkpoint**: Video plays in-app with visible video frames.

---

## Phase 6: Polish & Cross-Cutting Concerns

- [ ] T016 Run full test suite (`php artisan test --compact`) and fix any failures
- [ ] T017 Run PHPStan (`vendor/bin/phpstan analyse`) and fix any errors
- [ ] T018 Run Pint (`vendor/bin/pint --dirty`) on changed files
- [ ] T019 Run `npm run build` to verify frontend compiles
- [ ] T020 Run fallow on changed frontend files and address findings

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — T001 and T002 can run in parallel
- **Foundational (Phase 2)**: Depends on Setup (T003 needs MediaType from T001)
  - T003-T008 all touch different files — can run in parallel after Setup
- **US1 (Phase 3)**: Depends on Foundational
  - T009-T012 touch different backend files — mostly sequential (MediaProcessingService is the hub)
  - T013 (frontend) depends on T004 (types) from Foundational
- **US2 (Phase 4)**: Depends on Foundational (just needs the data model in place)
- **US3 (Phase 5)**: Depends on Foundational (T004 types update)
- **Polish (Phase 6)**: Depends on all user stories complete

### Parallel Opportunities

- T001 + T002 (Setup) in parallel
- T003, T004, T005, T006, T007, T008 (Foundational) all in parallel — different files
- T014 (US2 test) can run in parallel with US1 (different file)
- T015 (US3 frontend) can run in parallel with US1 backend work

---

## Implementation Strategy

### MVP First (User Story 1)

1. Complete Setup (MediaType enum + migration)
2. Complete Foundational (models, validator, converter, request)
3. Complete US1 (processing pipeline + UI selector)
4. **STOP and VALIDATE**: Upload a video file, choose audio-only, verify conversion works

### Incremental Delivery

1. Setup + Foundational → media_type field exists, validator accepts video
2. US1 → Users choose audio/video at creation → Deploy (MVP!)
3. US2 → RSS enclosures verified → Deploy
4. US3 → Video player in app → Deploy
5. Polish → Full suite green
