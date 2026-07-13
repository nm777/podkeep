# Tasks: Feed Type Ordering

**Input**: Design documents from `/specs/012-feed-type-ordering/`  
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/rss-feed.md

**Tests**: MANDATORY per constitution — all features require test coverage.

**Organization**: Tasks grouped by user story for independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `src/app/Http/`, `src/app/Models/`, `src/app/Enums/`
- **Frontend**: `src/resources/js/components/`, `src/resources/js/pages/`, `src/resources/js/types/`
- **Views**: `src/resources/views/`
- **Tests**: `src/tests/Feature/`, `src/tests/Unit/`
- **Migrations**: `src/database/migrations/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create the new enum and migration that all user stories depend on

- [ ] T001 Create `FeedType` enum with `Static = 'static'` and `Append = 'append'` cases, including `isStatic()` and `isAppend()` helper methods and `getDisplayName()`, in `src/app/Enums/FeedType.php`
- [ ] T002 Create migration to rename `episode_order` column to `feed_type` on feeds table and map values (`chronological` → `static`, `newest_first` → `append`), and add nullable `display_date` date column to `library_items` table, in `src/database/migrations/2026_07_12_000001_rename_episode_order_to_feed_type.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Update models, validation, and remove old enum — MUST be complete before any user story

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [ ] T003 [P] Update Feed model: replace `episode_order` fillable/cast with `feed_type` → `FeedType::class`, remove `EpisodeOrderType` import, in `src/app/Models/Feed.php`
- [ ] T004 [P] Update LibraryItem model: add `display_date` to fillable array and add `'display_date' => 'date'` to casts, in `src/app/Models/LibraryItem.php`
- [ ] T005 [P] Update FeedRequest validation: replace `episode_order` rule with `feed_type` rule using `Rule::enum(FeedType::class)`, in `src/app/Http/Requests/FeedRequest.php`
- [ ] T006 Delete `src/app/Enums/EpisodeOrderType.php` (replaced by FeedType)
- [ ] T007 [P] Update TypeScript types: replace `episode_order` with `feed_type: 'static' | 'append'` on Feed interface, add `display_date?: string` to LibraryItem interface, in `src/resources/js/types/index.d.ts`

**Checkpoint**: Foundation ready — enum, migration, models, validation, and types updated. User story implementation can now begin.

---

## Phase 3: User Story 1 — Choose Feed Type When Creating a Feed (Priority: P1) 🎯 MVP

**Goal**: Users select "Static" or "Append" feed type when creating/editing a feed, replacing the old Episode Order dropdown. Existing feeds are migrated automatically.

**Independent Test**: Create a new feed selecting "Static", verify it saves with `feed_type: static`. Create another selecting "Append", verify `feed_type: append`. Edit an existing migrated feed and confirm the type selector shows the correct value.

### Tests for User Story 1 (MANDATORY per constitution) ⚠️

- [ ] T008 [P] [US1] Feature test: creating a feed with `feed_type: static` stores correctly, and creating with `feed_type: append` stores correctly, in `src/tests/Feature/FeedTypeTest.php`
- [ ] T009 [P] [US1] Feature test: updating a feed's type from static to append and vice versa works correctly, in `src/tests/Feature/FeedTypeTest.php`
- [ ] T010 [P] [US1] Feature test: existing feeds with old `episode_order` values are migrated correctly (chronological→static, newest_first→append), in `src/tests/Feature/FeedTypeTest.php`

### Implementation for User Story 1

- [ ] T011 [US1] Update FeedController `store()` method: replace `episode_order` with `feed_type` using validated data, in `src/app/Http/Controllers/FeedController.php`
- [ ] T012 [US1] Update FeedController `update()` method: replace `episode_order` with `feed_type` in update call, in `src/app/Http/Controllers/FeedController.php`
- [ ] T013 [US1] Update FeedController `edit()` method: load items in feed-type-appropriate direction (static → sequence ASC, append → created_at DESC), in `src/app/Http/Controllers/FeedController.php`
- [ ] T014 [US1] Replace episode_order dropdown with feed type selector (radio buttons or select with "Static (Chapters)" and "Append (Ongoing)" options and descriptions), in `src/resources/js/components/feed-form-fields.tsx`
- [ ] T015 [US1] Update edit page form initialization: use `feed.feed_type` instead of `feed.episode_order`, in `src/resources/js/pages/feeds/edit.tsx`

**Checkpoint**: Users can select feed type during create/edit. Existing feeds are migrated. MVP functional.

---

## Phase 4: User Story 2 — Static Feed: Manual and Bulk Chapter Ordering (Priority: P2)

**Goal**: Static feeds provide drag-and-drop reordering plus bulk quick-sort buttons (Alphabetical, Reverse Alphabetical, Chronological, Reverse Chronological). RSS pubDates are sequence-derived so podcast apps display the user's arranged order.

**Independent Test**: Create a Static feed with 10 episodes in wrong order. Click "Chronological" quick-sort to fix the order. Drag one episode to fine-tune. Save. Verify RSS pubDates match the sequence (1-minute spacing from feed creation date).

### Tests for User Story 2 (MANDATORY per constitution) ⚠️

- [ ] T016 [P] [US2] Feature test: Static feed RSS pubDates are sequence-derived (feed.created_at + sequence minutes, 1-minute spacing), in `src/tests/Feature/FeedTypeTest.php`
- [ ] T017 [P] [US2] Feature test: Static feed RSS items appear in sequence ASC order, in `src/tests/Feature/FeedTypeTest.php`
- [ ] T018 [P] [US2] Feature test: reordering Static feed items via syncFeedItems updates sequences and regenerates RSS cache, in `src/tests/Feature/FeedTypeTest.php`

### Implementation for User Story 2

- [ ] T019 [US2] Update RssController: for Static feeds, query items by sequence ASC and use sequence-derived pubDate (`feed.created_at + sequence minutes`), in `src/app/Http/Controllers/RssController.php`
- [ ] T020 [US2] Update RSS Blade template: use feed-type-aware pubDate (Static → sequence-based, keeping existing fallback for `published_at`), in `src/resources/views/rss.blade.php`
- [ ] T021 [US2] Add quick-sort buttons (Alphabetical A→Z, Reverse Alphabetical Z→A, Chronological oldest-first, Reverse Chronological newest-first) to the edit page that sort `data.items` client-side, only visible for Static feeds, in `src/resources/js/pages/feeds/edit.tsx`
- [ ] T022 [US2] Update ShareController: for Static feeds, sort items by sequence ASC, in `src/app/Http/Controllers/ShareController.php`

**Checkpoint**: Static feeds have working quick-sort + drag-and-drop. RSS pubDates ensure correct ordering in podcast apps.

---

## Phase 5: User Story 3 — Append Feed: Newest Items at Top with Optional Date (Priority: P3)

**Goal**: Append feeds always show the most recently added episode first (newest pubDate). Users can optionally attach a display date to episodes that appears in the RSS description.

**Independent Test**: Create an Append feed. Add 3 episodes at different times. Verify RSS shows the most recently added first. Set a display date on one episode and verify it appears as `[Date]` prefix in the RSS description.

### Tests for User Story 3 (MANDATORY per constitution) ⚠️

- [ ] T023 [P] [US3] Feature test: Append feed RSS items appear in created_at DESC order (newest first), in `src/tests/Feature/FeedTypeTest.php`
- [ ] T024 [P] [US3] Feature test: Append feed RSS pubDates use feed_item.created_at, in `src/tests/Feature/FeedTypeTest.php`
- [ ] T025 [P] [US3] Feature test: Append feed episode with display_date shows `[Formatted Date]` prefix in RSS description, in `src/tests/Feature/FeedTypeTest.php`
- [ ] T026 [P] [US3] Feature test: switching feed type from Static to Append re-orders items by created_at DESC, in `src/tests/Feature/FeedTypeTest.php`

### Implementation for User Story 3

- [ ] T027 [US3] Update RssController: for Append feeds, query items by created_at DESC and use feed_item.created_at as pubDate, in `src/app/Http/Controllers/RssController.php`
- [ ] T028 [US3] Update RSS Blade template: for Append feeds, if `display_date` is set, prepend `[Formatted Date]` to the episode description, in `src/resources/views/rss.blade.php`
- [ ] T029 [US3] Add optional display_date input field to the edit page for Append feed episodes (date picker, only visible for Append feeds), in `src/resources/js/pages/feeds/edit.tsx`
- [ ] T030 [US3] Update ShareController: for Append feeds, sort items by created_at DESC (newest first), in `src/app/Http/Controllers/ShareController.php`
- [ ] T031 [US3] Update FeedController `update()` method: when switching to Append type, reassign sequences by created_at DESC, in `src/app/Http/Controllers/FeedController.php`
- [ ] T032 [US3] Add display_date to FeedRequest validation (nullable, date) and to the update flow for library items, in `src/app/Http/Requests/FeedRequest.php`

**Checkpoint**: Append feeds show newest-first with correct pubDates. Display date appears in RSS descriptions.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Update existing tests and ensure full suite passes

- [ ] T033 [P] Update `src/tests/Feature/FeedItemSyncTest.php`: replace `episode_order` references with `feed_type`, update test feed factories
- [ ] T034 [P] Update `src/tests/Feature/FeedEditTest.php`: replace `episode_order` with `feed_type`, update redirect assertions to `feeds.edit`
- [ ] T035 [P] Update `src/tests/Feature/StableFeedLinksTest.php`: update redirect assertions from `/feeds` to `/feeds/{id}/edit`
- [ ] T036 [P] Update `src/tests/Feature/EpisodeOrderTest.php`: rename to `FeedTypeTest.php` or merge into existing FeedTypeTest, update all episode_order references
- [ ] T037 Run full test suite (`php artisan test --compact`) and fix any failures
- [ ] T038 Run PHPStan (`vendor/bin/phpstan analyse`) and fix any errors
- [ ] T039 Run Pint (`vendor/bin/pint --dirty`) and fix any formatting issues
- [ ] T040 Run fallow on changed frontend files and address findings
- [ ] T041 Run `npm run build` and `npm run build:ssr` to verify frontend compiles

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion — BLOCKS all user stories
- **User Stories (Phase 3-5)**: All depend on Foundational phase completion
  - US1 (Phase 3) is the MVP — must complete first
  - US2 (Phase 4) depends on US1 (needs feed_type to exist on feeds)
  - US3 (Phase 5) depends on US1 (needs feed_type to exist on feeds)
  - US2 and US3 can be done in parallel after US1
- **Polish (Phase 6)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Depends on Foundational. No dependencies on other stories. **MVP**.
- **User Story 2 (P2)**: Depends on US1 (feed_type must be selectable). Static-specific RSS/controller changes.
- **User Story 3 (P3)**: Depends on US1 (feed_type must be selectable). Append-specific RSS/controller changes. Shares RssController/RSS template with US2 but different branches of logic.

### Within Each User Story

- Tests written FIRST and confirmed to FAIL
- Backend models/controllers before frontend
- RSS template changes after controller logic
- Frontend UI after backend API is ready
- Story complete before moving to next priority

### Parallel Opportunities

- T001 and T002 (Setup) can run in parallel
- T003, T004, T005, T007 (Foundational) can run in parallel — different files
- T008, T009, T010 (US1 tests) can run in parallel
- T016, T017, T018 (US2 tests) can run in parallel
- T023, T024, T025, T026 (US3 tests) can run in parallel
- T033, T034, T035, T036 (Polish — existing test updates) can run in parallel
- US2 and US3 can run in parallel after US1 (different branches of controller/template logic)

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (FeedType enum + migration)
2. Complete Phase 2: Foundational (models, validation, types)
3. Complete Phase 3: User Story 1 (feed type selection)
4. **STOP and VALIDATE**: Create/edit feeds with both types, verify migration
5. Deploy if ready — existing feeds work with new types

### Incremental Delivery

1. Setup + Foundational → Migration runs, existing feeds converted
2. Add US1 → Users can choose feed type → Deploy (MVP!)
3. Add US2 → Static feeds get quick-sort + correct pubDates → Deploy
4. Add US3 → Append feeds get newest-first + display dates → Deploy
5. Polish → All existing tests updated, full suite green

---

## Notes

- The migration (T002) is the highest-risk task — test it thoroughly against production data
- RssController and rss.blade.php are shared between US2 and US3 but use different branches of logic (Static vs Append)
- The `published_at` field on LibraryItem remains for quick-sort chronological mode but is no longer used for pubDate
- Production deployment requires clearing `storage/framework/views/` (RSS template change)
