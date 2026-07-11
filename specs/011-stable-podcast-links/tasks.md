# Tasks: Stable Podcast Links (Links Survive Renames)

**Input**: Design documents from `/specs/011-stable-podcast-links/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: MANDATORY per constitution (Core Principle III — TDD is non-negotiable).

**Organization**: Tasks grouped by user story. See the note below on the shared implementation.

> ## IMPORTANT: Single shared implementation change
>
> All three user stories (US1, US2, US3) are facets of **one** root-cause fix:
> the web `FeedController::update()` regenerates `slug` from the title on every
> save (`src/app/Http/Controllers/FeedController.php:86`), breaking every
> previously shared RSS/share link. Removing that one line makes the slug
> write-once, which simultaneously delivers all three stories.
>
> - **US1** (rename keeps links alive) — the fix.
> - **US2** (non-title edits don't change the link) — the SAME fix (the bug
>   overwrote the slug even on description/visibility edits).
> - **US3** (consistent web + API behavior) — the fix unifies the web path with
>   the API path, which already leaves the slug untouched.
>
> The implementation lands once (T004, in the US1/MVP phase). US2 and US3 phases
> add their story-specific **regression-guard tests** and verify the fix covers
> their acceptance scenarios. No migration, no frontend change, no API change.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `src/app/Http/Controllers/`, `src/app/Models/`
- **Tests**: `src/tests/Feature/`

---

## Phase 1: Setup (Baseline)

**Purpose**: Confirm a green starting point so any later failure is attributable to this feature's change.

- [ ] T001 Run existing feed/RSS/share test suites to confirm green baseline via `docker compose exec app php artisan test tests/Feature/FeedEditTest.php tests/Feature/FeedManagementTest.php tests/Feature/ShareControllerTest.php tests/Feature/RssFeedTest.php` (run from the production compose dir `/home/nate/Documents/docker/podkeep/` per AGENTS.md)

---

## Phase 2: Foundational (TDD Red — blocking)

**Purpose**: Write the failing test suite that captures ALL acceptance scenarios for ALL three stories. The bug is present, so the slug-stability assertions MUST fail here.

**⚠️ CRITICAL**: This phase MUST complete (red confirmed) before the fix is applied.

- [ ] T002 Create `src/tests/Feature/StableFeedLinksTest.php` (Pest) containing all scenarios below, modeled on the style of `src/tests/Feature/FeedEditTest.php`. Use `Feed::factory()` and `actingAs()`. Scenarios to include:
  - **US1 scenarios**:
    - `it('keeps the slug unchanged when renaming a feed via the web update')` — PUT `/feeds/{id}` with a new title; assert DB `slug` equals the original slug and `title` updated.
    - `it('keeps the original RSS URL resolving after a rename, showing the new title')` — rename; GET `/rss/{user_guid}/{original_slug}`; assert 200, `Content-Type` XML, body contains the NEW title.
    - `it('keeps the original share URL resolving after a rename')` — rename; GET `/share/{user_guid}/{original_slug}`; assert 200, Inertia page, feed title prop is the NEW title.
    - `it('keeps the slug unchanged across multiple sequential renames')` — rename twice; assert slug still equals the original; original RSS URL still 200.
  - **US2 scenarios**:
    - `it('keeps the slug unchanged when editing only the description')` — PUT with same title, new description; assert slug unchanged.
    - `it('keeps the slug unchanged when toggling is_public')` — PUT with same title, flipped `is_public`; assert slug unchanged.
  - **US3 scenarios**:
    - `it('keeps the slug unchanged when renaming via the API')` — PUT `/api/v1/feeds/{id}` with new title; assert 200, response JSON `slug` and `user_guid` unchanged, `title` updated.
    - `it('shows the latest title after renames via both web and API')` — rename via web then via API; GET original share URL; assert latest title shown.
    - `it('still prevents non-owners from renaming a feed')` — non-owner PUT `/feeds/{id}`; assert 403, slug/title unchanged (FR-006 regression guard).
- [ ] T003 Sync the new test file into the container and run it to confirm the slug-stability assertions FAIL (red): `docker compose cp src/tests/Feature/StableFeedLinksTest.php app:/var/www/html/tests/Feature/StableFeedLinksTest.php` then `docker compose exec app php artisan test tests/Feature/StableFeedLinksTest.php` (run from `/home/nate/Documents/docker/podkeep/`)

**Checkpoint**: All slug-stability assertions are red. Ready for the fix.

---

## Phase 3: User Story 1 — Rename Keeps RSS Subscriptions Alive (Priority: P1) 🎯 MVP

**Goal**: Renaming a podcast does not alter or invalidate its public RSS/share link; existing subscriptions keep receiving episodes.

**Independent Test**: Create a feed, note its RSS link, rename it, and open the original RSS link — it must still return a valid feed whose `<title>` is the new name.

### Implementation for User Story 1

- [ ] T004 [US1] Apply the root-cause fix in `src/app/Http/Controllers/FeedController.php`: (1) in `update()`, delete the line `'slug' => $this->generateUniqueSlug($validated['title'], $feed->id),` from the `$feed->update([...])` array (the `title` assignment on the line above stays); (2) in the private `generateUniqueSlug()`, remove the now-dead `?int $excludeFeedId = null` parameter and both `if ($excludeFeedId) { ... }` blocks (its only remaining caller `store()` does not pass it). Do NOT touch the API controller — it already leaves the slug untouched.
- [ ] T005 [US1] Sync the controller change and run US1's scenarios to confirm green: `docker compose cp src/app/Http/Controllers/FeedController.php app:/var/www/html/app/Http/Controllers/FeedController.php` then `docker compose exec app php artisan test tests/Feature/StableFeedLinksTest.php` (run from `/home/nate/Documents/docker/podkeep/`)

**Checkpoint**: User Story 1 is fully functional — the original RSS/share URL resolves after a rename and reflects the new title. (MVP delivered.)

---

## Phase 4: User Story 2 — Editing Details Never Changes the Link (Priority: P2)

**Goal**: Saving a non-name edit (description, visibility, etc.) leaves the public link byte-for-byte unchanged.

**Independent Test**: Capture a feed's exact public link, edit its description and toggle visibility (without changing the title), and confirm the slug/link is identical after each save.

> Implementation is already covered by T004 (the fix removed slug regeneration from every `update()`, not just title changes). This phase verifies US2's scenarios pass and locks them in as regression guards.

- [ ] T006 [US2] Run US2's scenarios (description edit, is_public toggle) to confirm green: `docker compose exec app php artisan test tests/Feature/StableFeedLinksTest.php --filter='description|is_public'` (run from `/home/nate/Documents/docker/podkeep/`). If any fail, T004 did not fully remove slug regeneration — re-check `update()`.

**Checkpoint**: User Stories 1 AND 2 both hold — no save of any kind changes the public link.

---

## Phase 5: User Story 3 — Consistent Renaming Across All Methods (Priority: P3)

**Goal**: Renaming via the web screen or the API yields identical link-stable behavior; the new name appears on the share page; non-owners cannot rename.

**Independent Test**: Rename a feed via the API, then open the original share link — it must resolve and show the new name; a non-owner's rename attempt must be rejected.

> Implementation is already covered by T004 (web path now matches the API path, which has always left the slug untouched). This phase verifies US3's parity scenarios pass.

- [ ] T007 [US3] Run US3's scenarios (API rename, web+API sequence shows latest title, non-owner 403) to confirm green: `docker compose exec app php artisan test tests/Feature/StableFeedLinksTest.php --filter='API|both web|non-owners'` (run from `/home/nate/Documents/docker/podkeep/`)

**Checkpoint**: All three user stories are independently satisfied and verified.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Ensure no regressions and that quality gates pass before commit.

- [ ] T008 [P] Run the related existing suites to confirm no regression via `docker compose exec app php artisan test tests/Feature/FeedEditTest.php tests/Feature/FeedManagementTest.php tests/Feature/ShareControllerTest.php tests/Feature/RssFeedTest.php` (run from `/home/nate/Documents/docker/podkeep/`). In particular `FeedEditTest`'s "allows feed owner to update feed details" must still pass (title still updates; only slug stability is new).
- [ ] T009 [P] Run PHPStan and address any findings via `docker compose exec app ./vendor/bin/phpstan` (run from `/home/nate/Documents/docker/podkeep/`)
- [ ] T010 Commit the controller change plus the new test file on the `011-stable-podcast-links` branch (do NOT run `npm run build` inside the container — there is no frontend change). Inspect `git status`/`git diff` before committing; stage only `src/app/Http/Controllers/FeedController.php` and `src/tests/Feature/StableFeedLinksTest.php`.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately.
- **Foundational (Phase 2)**: Depends on Setup — BLOCKS all user stories (TDD red must be confirmed first).
- **US1 (Phase 3)**: Depends on Foundational. Carries the single shared implementation fix.
- **US2 (Phase 4)**: Depends on US1 (the fix). Verification only.
- **US3 (Phase 5)**: Depends on US1 (the fix). Verification only.
- **Polish (Phase 6)**: Depends on all user story phases being verified green.

### User Story Dependencies

- **US1 (P1)**: After Foundational. No dependency on other stories. Delivers the fix.
- **US2 (P2)**: After US1's fix. Independently testable (its scenarios pass because of the same fix).
- **US3 (P3)**: After US1's fix. Independently testable.

### Within Each User Story

- Tests (Phase 2) written and confirmed RED before any implementation.
- Implementation (T004) before per-story verification.
- Story verified green before moving to the next.

### Parallel Opportunities

- T008 and T009 (regression suite vs PHPStan) are independent checks — may run together.
- Little other parallelism: this is a single-file fix plus a single test file, so most tasks are sequential by necessity.

---

## Parallel Example: Polish

```bash
# These two quality checks are independent and may be launched together:
Task: "Run regression suites (T008)"
Task: "Run PHPStan (T009)"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (baseline green).
2. Complete Phase 2: Foundational (tests red).
3. Complete Phase 3: User Story 1 (apply fix, US1 green).
4. **STOP and VALIDATE**: Rename a feed; confirm the original RSS/share URL still resolves and shows the new title.
5. Deploy/demo if ready — MVP already prevents link breakage on rename.

### Incremental Delivery

1. Setup + Foundational → failing tests in place.
2. US1 → fix applied → RSS subscriptions survive renames (MVP).
3. US2 → verify non-title edits also keep the link stable.
4. US3 → verify web/API parity and authorization.
5. Polish → full regression + static analysis + commit.

---

## Notes

- [P] tasks = different files / independent checks, no dependencies.
- [Story] label maps a task to its user story for traceability.
- The single implementation change (T004) is shared by all three stories; US2/US3 are verification + regression-guard phases, not separate code changes.
- All `docker compose` commands run from the production compose directory `/home/nate/Documents/docker/podkeep/` (per AGENTS.md — the dev repo shares the compose project name with production and its bind mount is stale).
- Verify tests fail (red) before implementing (TDD).
- Commit only after quality gates (T008, T009) are clean.
