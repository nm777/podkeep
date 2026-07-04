---

description: "Task list for Per-Feed Episode Ordering feature"
---

# Tasks: Per-Feed Episode Ordering

**Input**: Design documents from `/specs/009-feed-episode-order/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md

**Tests**: MANDATORY per constitution — all features require test coverage. Tests are written FIRST (Red-Green-Refactor).

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2)
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `src/app/Http/`, `src/app/Models/`, `src/app/Enums/`
- **Frontend**: `src/resources/js/components/`, `src/resources/js/pages/`, `src/resources/js/types/`
- **Tests**: `src/tests/Feature/`
- **Routes**: `src/routes/`
- **Database**: `src/database/migrations/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create enum and migration that all user stories depend on

- [X] T001 Create EpisodeOrderType enum in `src/app/Enums/EpisodeOrderType.php` with values `NewestFirst = 'newest_first'` and `Chronological = 'chronological'`, following the ProcessingStatusType pattern (backed string enum, TitleCase keys, `getDisplayName()` returning 'Newest First'/'Chronological', `isNewestFirst(): bool` and `isChronological(): bool` helpers)
- [X] T002 [P] Create migration to add `episode_order` column to feeds table in `src/database/migrations/` — string column (length 20), default `'newest_first'`, placed after `is_public`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Model changes that MUST be complete before any user story rendering changes work

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T003 Update Feed model in `src/app/Models/Feed.php` — add `'episode_order'` to `$fillable` array, add `casts()` method with `'episode_order' => EpisodeOrderType::class`, and add `->orderBy('sequence')` to the `items()` relationship (currently a bare `hasMany` with no ordering — this fixes the latent RSS ordering bug)
- [X] T004 Run the migration via `php artisan migrate --force` in the container and verify the `episode_order` column exists on the feeds table

**Checkpoint**: Foundation ready — Feed model has the new field, enum is available, items relationship orders by sequence.

---

## Phase 3: User Story 1 — Episode Order Toggle and Manual Reordering (Priority: P1) 🎯 MVP

**Goal**: Users can set a feed to "chronological" or "newest first" order, manually drag-and-drop episodes to fix ordering, and see the corrected order across RSS feed, share player, and edit page.

**Independent Test**: Create a feed, upload episodes in scrambled order, set feed to chronological, open edit page, drag episodes into correct order, save, and verify the corrected order appears in RSS feed and share player.

### Tests for User Story 1 (MANDATORY per constitution) ⚠️

> **NOTE**: Write these tests FIRST, ensure they FAIL before implementation.

- [X] T005 [P] [US1] Feature test for episode_order field storage in `src/tests/Feature/EpisodeOrderTest.php` — test creating a feed with `episode_order: 'chronological'` stores correctly, test default is `'newest_first'`, test updating episode_order works, test invalid enum value is rejected with 422
- [X] T006 [P] [US1] Feature test for RSS feed ordering in `src/tests/Feature/EpisodeOrderTest.php` — test a chronological feed outputs items by sequence ASC (chapter 1 first) in the RSS XML, test a newest_first feed outputs items by sequence DESC (newest first)
- [X] T007 [P] [US1] Feature test for share player ordering in `src/tests/Feature/EpisodeOrderTest.php` — test the share player endpoint returns episodes in the feed's chosen order direction
- [X] T008 [P] [US1] Feature test for feed edit page loading order in `src/tests/Feature/EpisodeOrderTest.php` — test the edit page loads items ordered by sequence ASC regardless of the feed's display mode (so drag-and-drop initial positions are predictable)
- [X] T009 [P] [US1] Feature test for API episode_order exposure in `src/tests/Feature/Api/V1/FeedControllerTest.php` — test FeedResource includes `episode_order` field, test API create/update accepts and persists `episode_order`

### Implementation for User Story 1

- [X] T010 [P] [US1] Add `episode_order` validation rule to `src/app/Http/Requests/FeedRequest.php` — add `'episode_order' => ['nullable', 'string', Rule::enum(EpisodeOrderType::class)]` after `is_public` rule
- [X] T011 [P] [US1] Update web FeedController in `src/app/Http/Controllers/FeedController.php` — add `'episode_order' => $validated['episode_order'] ?? 'newest_first'` to the `create()` array in `store()`, and `'episode_order' => $validated['episode_order'] ?? $feed->episode_order` to the `update()` array in `update()`
- [X] T012 [US1] Update RssController in `src/app/Http/Controllers/RssController.php` — load feed items ordered by sequence in the feed's direction: `$direction = $feed->episode_order->isChronological() ? 'asc' : 'desc'` then eager-load with `['items' => fn ($q) => $q->orderBy('sequence', $direction), ...]` (see `specs/009-feed-episode-order/data-model.md` rendering section)
- [X] T013 [US1] Update ShareController in `src/app/Http/Controllers/ShareController.php` — replace the hardcoded `$feed->items->sortBy('sequence')` with direction-aware sorting: use `sortByDesc('sequence')` for newest_first feeds and `sortBy('sequence')` for chronological feeds, based on `$feed->episode_order`
- [X] T014 [US1] Update web FeedController `edit()` method in `src/app/Http/Controllers/FeedController.php` — change `$feed->load(['items.libraryItem', ...])` to `$feed->load(['items' => fn ($q) => $q->orderBy('sequence', 'asc'), 'items.libraryItem', 'items.libraryItem.mediaFile'])` so the edit page always loads items in sequence ASC order (the relationship default from T003 provides this, but override explicitly to be safe since the drag-and-drop hook uses 0-based indexing)
- [X] T015 [P] [US1] Add `episode_order` validation to API form requests — add `'episode_order' => ['nullable', 'string', Rule::enum(EpisodeOrderType::class)]` to both `src/app/Http/Requests/Api/V1/StoreFeedRequest.php` and `src/app/Http/Requests/Api/V1/UpdateFeedRequest.php`
- [X] T016 [P] [US1] Update API FeedController and FeedResource — add `'episode_order' => $validated['episode_order'] ?? 'newest_first'` to the `create()` array in `src/app/Http/Controllers/Api/V1/FeedController.php` `store()` method, and add `'episode_order' => $this->episode_order` to `src/app/Http/Resources/FeedResource.php` `toArray()` output
- [X] T017 [P] [US1] Update feed form fields component in `src/resources/js/components/feed-form-fields.tsx` — extend `FeedFormFieldsProps` to include `episode_order` in data type and setData union, add a Select component (using existing UI Select or a simple `<select>`) below the `is_public` checkbox with options "Newest First" and "Chronological"
- [X] T018 [US1] Update feed edit page and TypeScript types — add `episode_order: feed.episode_order || 'newest_first'` to the `useForm` initial state in `src/resources/js/pages/feeds/edit.tsx`, and add `episode_order?: string` to the Feed type in `src/resources/js/types/index.d.ts`

**Checkpoint**: User Story 1 is fully functional — users can toggle episode order, manually reorder via drag-and-drop, and see correct ordering in RSS feed, share player, and edit page.

---

## Phase 4: User Story 2 — Auto-Append for Chronological Feeds (Priority: P2)

**Goal**: New episodes added to chronological feeds automatically receive the next sequence number (append to end), while newest_first feeds get new episodes at the top.

**Independent Test**: Create a chronological feed with episodes, upload a new episode, verify it appears at the end. Create a newest_first feed, upload a new episode, verify it appears at the top.

**Note**: Per research R3, the existing `AddLibraryItemToFeedsJob` uses `max(sequence) + 1` which is already correct for both modes. This phase is verification-only — no code changes expected, just a test to confirm the behavior.

### Tests for User Story 2

- [X] T019 [P] [US2] Feature test for auto-append sequence behavior in `src/tests/Feature/EpisodeOrderTest.php` — test that adding a new item to a chronological feed assigns `max(sequence) + 1` (appears at end in ASC order), and adding to a newest_first feed also assigns `max(sequence) + 1` (appears at top in DESC order), verifying both modes produce the correct visual position with the existing `AddLibraryItemToFeedsJob` logic

**Checkpoint**: Both user stories are independently functional — the complete episode ordering feature is available.

---

## Phase 5: Polish & Cross-Cutting Concerns

**Purpose**: Formatting and regression validation

- [X] T020 [P] Run `vendor/bin/pint --dirty` in src/ to format all modified PHP files
- [X] T021 Run full test suite via `php artisan test --no-interaction` in src/ and verify all existing tests still pass alongside new episode order tests

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — T001 and T002 can run in parallel
- **Foundational (Phase 2)**: Depends on T001 (enum) and T002 (migration) — T003 needs the enum, T004 needs the migration
- **User Story 1 (Phase 3)**: Depends on Foundational completion — needs model changes
- **User Story 2 (Phase 4)**: Depends on US1 — needs `episode_order` to exist on feeds
- **Polish (Phase 5)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational. No dependency on US2.
- **User Story 2 (P2)**: Depends on US1 (needs `episode_order` field to exist). Likely requires zero code changes — just a verification test.

### Within User Story 1

- Tests T005-T009: All [P] — different test scenarios, can be written simultaneously
- T010-T011 (web form request + controller): [P] — different files
- T012-T013 (RSS + Share controllers): Sequential within the story — T012 depends on Feed model changes from Phase 2
- T014 (edit controller): Same file as T011 — must run after T011
- T015-T016 (API): [P] — different files from web tasks
- T017-T018 (frontend): [P] — different files, can run alongside backend tasks

### Parallel Opportunities

- T001 + T002: Setup tasks — different files
- T005-T009: All US1 tests — can be written simultaneously
- T010-T011 + T015-T016 + T017-T018: Backend and frontend implementation can run in parallel (different files)
- T019: US2 test — can run after US1 implementation is complete

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001-T002 — enum + migration)
2. Complete Phase 2: Foundational (T003-T004 — model changes + run migration)
3. Complete Phase 3: User Story 1 (T005-T018 — tests + full implementation)
4. **STOP and VALIDATE**: Set a feed to chronological, reorder episodes, verify RSS + share player + edit page
5. Deploy if ready — users can now control episode ordering

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → Deploy (MVP — order toggle + manual reordering)
3. Add User Story 2 → Test independently → Deploy (auto-append verification)

---

## Notes

- [P] tasks = different files, no dependencies on incomplete tasks
- The `sequence` column already exists on `feed_items` — NO schema changes to that table
- The drag-and-drop reorder hook (`useFeedItemReorder`) and save logic (`syncFeedItems`) already work — no changes needed
- The `AddLibraryItemToFeedsJob` already assigns `max(sequence) + 1` — already correct for both order modes (US2 is verification-only)
- Key bug fix: `Feed::items()` currently has no `orderBy` — adding `orderBy('sequence')` fixes the RSS feed's latent ordering bug
- Run `vendor/bin/pint --dirty` after PHP changes and `npm run build` after frontend changes (for Vite manifest)
