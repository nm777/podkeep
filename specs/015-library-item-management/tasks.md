---

description: "Task list for feature 015-library-item-management"
---

# Tasks: Improved Library Item Management

**Input**: Design documents from `/specs/015-library-item-management/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: MANDATORY per constitution (TDD, red-green). This project has no JS test runner; frontend behavior is covered by Pest feature tests asserting Inertia props + DB state (the existing convention), plus the manual verification steps in `quickstart.md`. No JS test harness is introduced.

**Organization**: Tasks grouped by user story. US1 (P1, tabbed searchable picker) and US2 (P2, hide feeds) are fully independent — different files; the only shared file is `FeedController.php`, touched in distinct methods.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependency on an incomplete task)
- **[Story]**: Which user story this task belongs to (US1, US2)
- Exact file paths included in every description

## Path Conventions

- **Backend**: `src/app/Http/Controllers/`, `src/app/Http/Requests/`, `src/app/Models/`
- **Frontend**: `src/resources/js/components/`, `src/resources/js/pages/`, `src/resources/js/types/`
- **DB**: `src/database/migrations/`, `src/database/factories/`
- **Tests**: `src/tests/Feature/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Confirm the working environment before any code change.

- [X] T001 Verify branch `015-library-item-management` is checked out and `src/vendor/` is in sync with `src/composer.json` (if `Class not found`/stale, run the ephemeral `composer install` command from `AGENTS.md`). No code change — environment readiness only.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared infrastructure that MUST be complete before ANY user story.

**No foundational tasks required.** US1 and US2 share no entity, migration, type, or component:
- US1 touches `FeedController::edit()` + `resources/js/pages/feeds/edit.tsx` only.
- US2 touches `Feed` model/migration/request + `FeedController::store()/update()` + `feed-form-fields.tsx` + `dashboard.tsx` + the `Feed` type.
- The single shared file is `src/app/Http/Controllers/FeedController.php`, edited in **different methods** by each story — sequence the stories (or the two controller edits) to avoid merge conflicts.

**Checkpoint**: Foundation ready — user story implementation can begin (independently or in parallel).

---

## Phase 3: User Story 1 - Tabbed, Searchable Media Picker on the Feed Editor (Priority: P1) MVP

**Goal**: Replace the tiny, non-searchable "Add Library Items" box on the feed edit page with a two-tab layout ("Feed Items" / "Add Media"). The "Add Media" tab is a tall, scrollable, title-searchable list. The backend stops capping `userLibraryItems` at 100 so the full personal library is searchable.

**Independent Test**: Open a feed's edit page → switch to the "Add Media" tab → type part of an item title → see filtered matches in a tall scrollable list → click `+` → the item moves to "Feed Items". Separately, a user with >100 library items sees all of them available to add.

### Tests for User Story 1 (write first, confirm RED)

> The existing `FeedEditPaginationTest.php` currently **codifies the 100-cap** we are removing — it is the red test.

- [X] T002 [P] [US1] Rewrite the assertion in `src/tests/Feature/FeedEditPaginationTest.php` to assert the edit page returns the FULL library (with 120 items created, `count(userLibraryItems) === 120`, each carrying `media_file`), inverting the current `toBeLessThanOrEqual(100)`. Confirm it fails before implementation.

### Implementation for User Story 1

- [X] T003 [US1] In `src/app/Http/Controllers/FeedController.php` `edit()`, remove `->limit(100)` from the `userLibraryItems` query and add `->orderBy('created_at', 'desc')` so the full library is returned in a stable order for client-side search. (Makes T002 pass.)
- [X] T004 [US1] Refactor `src/resources/js/pages/feeds/edit.tsx`: replace the stacked "Feed Items" + "Add Library Items" sections with a two-tab layout ("Feed Items" / "Add Media") using local `useState` for the active tab; mirror the inline tab pattern at `src/resources/js/pages/dashboard.tsx:100`. Move the existing feed-items list (search, drag-reorder, remove, display-date) into the "Feed Items" tab unchanged.
- [X] T005 [US1] In `src/resources/js/pages/feeds/edit.tsx`, implement the "Add Media" tab: render `availableLibraryItems` (already excludes items on the feed) via the existing `LibraryItemInfo` component + a `+` button (reuse `addLibraryItem`), in a tall scroll container (`max-h-[60vh]`, replacing `max-h-48`), filtered live by a `SearchInput` + `useDebouncedValue` on title — mirroring the existing feed-items search. Empty states: "No items match your search." / "All library items are already in this feed."

**Checkpoint**: User Story 1 is fully functional and independently testable. This is the MVP — stop and validate here if desired.

---

## Phase 4: User Story 2 - Hide Feeds from the Add-Media Selector (Priority: P2)

**Goal**: Add a per-feed `is_hidden_from_selector` flag (default false = shown). Hidden feeds are omitted from the `MediaUploadButton`/`FeedSelector` picker only; the dashboard feed list, RSS, sharing, and existing memberships are unaffected.

**Independent Test**: Edit a feed → uncheck "Show in Add Media list" → save → open the Add Media dialog → that feed is absent from the selector; it still appears on the dashboard; its RSS still lists its items. Re-check → it reappears in the picker.

### Tests for User Story 2 (write first, confirm RED)

- [X] T006 [P] [US2] Add tests (in `src/tests/Feature/FeedEditTest.php`, extending existing conventions): (a) `PUT /feeds/{feed}` with `is_hidden_from_selector => true` persists the flag (`assertDatabaseHas`); (b) a newly created feed defaults to `is_hidden_from_selector => false`; (c) a non-boolean value triggers a session validation error on `is_hidden_from_selector`.
- [X] T007 [P] [US2] Add a test in `src/tests/Feature/DashboardTest.php` asserting the shared Inertia `feeds` prop STILL includes a feed created with `is_hidden_from_selector => true` (codifies FR-008: hiding does not affect the dashboard list / shared prop).

### Implementation for User Story 2

- [X] T008 [US2] Create migration `src/database/migrations/2026_07_24_000001_add_is_hidden_from_selector_to_feeds_table.php` adding `$table->boolean('is_hidden_from_selector')->default(false)->after('is_public')`. Additive only.
- [X] T009 [P] [US2] In `src/app/Models/Feed.php`, add `is_hidden_from_selector` to `$fillable` and to `casts()` as `'is_hidden_from_selector' => 'boolean'`.
- [X] T010 [P] [US2] In `src/app/Http/Requests/FeedRequest.php`, add `'is_hidden_from_selector' => ['boolean']` to `rules()`.
- [X] T011 [P] [US2] In `src/database/factories/FeedFactory.php`, add `'is_hidden_from_selector' => false` to the `definition()` array.
- [X] T012 [P] [US2] In `src/resources/js/types/index.d.ts`, add `is_hidden_from_selector: boolean` to the `Feed` interface.
- [X] T013 [US2] In `src/app/Http/Controllers/FeedController.php`, persist the flag in `store()` (`'is_hidden_from_selector' => $validated['is_hidden_from_selector'] ?? false`) and `update()` (same). (Depends on T009, T010; makes T006 pass.)
- [X] T014 [US2] Wire the flag into the feed form: in `src/resources/js/components/feed-form-fields.tsx` add a "Show in Add Media list" checkbox (checked = shown, bound to the inverse of `is_hidden_from_selector`), updating the component's `data`/`setData` typings; add `is_hidden_from_selector` to the initial `useForm` data in both `src/resources/js/components/create-feed-form.tsx` (default `false`) and `src/resources/js/pages/feeds/edit.tsx` (initial `feed.is_hidden_from_selector`). (Depends on T012.)
- [X] T015 [P] [US2] In `src/resources/js/pages/dashboard.tsx`, pass `feeds={feeds.filter((f) => !f.is_hidden_from_selector)}` to `MediaUploadButton`; leave the dashboard `FeedCard` list mapping over the full `feeds` array unchanged. (Depends on T012.)

**Checkpoint**: User Stories 1 AND 2 are both independently functional.

---

## Phase 5: Polish & Cross-Cutting Concerns

**Purpose**: Quality gates mandated by `AGENTS.md`, run after code is complete.

- [X] T016 [P] Run PHPStan: `docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html --entrypoint vendor/bin/phpstan podkeep-app:latest analyse --no-progress`; resolve all findings.
- [X] T017 [P] Run Pint on changed PHP: `docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html --entrypoint vendor/bin/pint podkeep-app:latest --dirty`.
- [X] T018 [P] Run fallow on changed JS/TS files; resolve all findings.
- [X] T019 Run the affected Pest suites — `FeedEditTest`, `FeedEditPaginationTest`, `FeedManagementTest`, `DashboardTest`, `RssFeedTest` — via the ephemeral `docker run --rm ... artisan test` command from `AGENTS.md`; then complete the manual verification steps in `src/specs/015-library-item-management/quickstart.md`.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately.
- **Foundational (Phase 2)**: No tasks; independence documented. Blocks nothing.
- **User Stories (Phase 3, 4)**: Independent of each other. May run sequentially (P1 → P2, recommended) or in parallel by different developers (coordinate the single shared file `FeedController.php`).
- **Polish (Phase 5)**: After the desired stories are complete.

### User Story Dependencies

- **US1 (P1)**: T002 (red test) → T003 (controller) → T004 (tabs) → T005 (Add Media tab). No dependency on US2.
- **US2 (P2)**: T006/T007 (red tests) → T008 (migration) → T009/T010/T011/T012 (parallel: model, request, factory, type) → T013 (controller) → T014 (form) / T015 (dashboard). No dependency on US1.

### Within Each User Story

- Write tests first; confirm they FAIL before implementing.
- Schema → model → request → controller (backend) before frontend that consumes it.
- Core implementation before integration/UI wiring.

### Parallel Opportunities

- T002 (US1 test) and T006/T007 (US2 tests) are independent test files → parallel.
- Within US2 implementation: T009, T010, T011, T012 are different files with no write-time dependency → parallel.
- T014 and T015 are different frontend files → parallel (both depend only on T012).
- US1 and US2 can be developed in parallel by different developers (coordinate `FeedController.php` method edits).
- Polish tasks T016/T017/T018 are different tools → parallel (run after all code lands).

---

## Parallel Example: User Story 2

```bash
# Red tests together:
Task T006: "US2 persistence/default/validation tests in src/tests/Feature/FeedEditTest.php"
Task T007: "US2 shared-feeds-prop test in src/tests/Feature/DashboardTest.php"

# After migration T008, these independent files together:
Task T009: "Add is_hidden_from_selector to src/app/Models/Feed.php"
Task T010: "Add boolean rule to src/app/Http/Requests/FeedRequest.php"
Task T011: "Add default to src/database/factories/FeedFactory.php"
Task T012: "Add field to Feed in src/resources/js/types/index.d.ts"
```

---

## Implementation Strategy

### MVP First (User Story 1 only)

1. T001 — verify environment.
2. T002 → T003 → T004 → T005 — ship the tabbed, searchable media picker.
3. **STOP and VALIDATE**: open the feed edit page and run the US1 independent test.
4. Commit; deploy/demo if desired. The biggest usability pain is already resolved.

### Incremental Delivery

1. Setup → US1 (MVP: tabbed searchable picker) → validate → commit.
2. US2 (hide feeds) → validate → commit.
3. Polish: PHPStan + Pint + fallow + full affected Pest suite.

### Parallel Team Strategy

- Developer A: US1 (`feeds/edit.tsx` + `FeedController::edit`).
- Developer B: US2 (model/migration/request + `FeedController::store/update` + form + dashboard).
- Coordinate the one shared file (`FeedController.php`) — different methods, low conflict risk.

---

## Notes

- [P] tasks = different files, no write-time dependency on an incomplete task.
- [Story] label maps a task to its user story for traceability.
- Each user story is independently completable and testable.
- Confirm tests fail before implementing; commit after each task or logical group.
- Stop at any checkpoint to validate a story independently.
- Avoid: vague tasks, same-file conflicts (e.g., T004 & T005 both edit `feeds/edit.tsx` — run sequentially), cross-story dependencies that break independence.
