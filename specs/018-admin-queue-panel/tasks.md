---

description: "Task list for feature 018-admin-queue-panel"
---

# Tasks: Admin Queue Job Panel

**Input**: Design documents from `/specs/018-admin-queue-panel/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: MANDATORY per constitution (TDD, red-green). The project has no JS test runner; backend behavior is covered by Pest feature tests.

**Organization**: Tasks grouped by user story. US1 (view) is the foundation — the admin nav + queue controller + page. US2 (manage) adds actions to the same controller/page. US3 (recently completed) adds a completion log + view section. All three are independently testable.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependency on an incomplete task)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)
- Exact file paths included in every description

## Path Conventions

- **Backend**: `src/app/Http/Controllers/`, `src/app/Console/Commands/`, `src/app/Listeners/`, `src/routes/`
- **Frontend**: `src/resources/js/layouts/`, `src/resources/js/pages/`
- **DB**: `src/database/migrations/`
- **Tests**: `src/tests/Feature/`

---

## Phase 1: Setup

- [X] T001 Verify branch `018-admin-queue-panel` is checked out and `src/vendor/` is in sync. No code change — environment readiness only.

---

## Phase 2: Foundational (Admin Nav)

**Purpose**: The extensible admin navigation shared by all stories.

- [X] T002 Extend `src/resources/js/layouts/admin-layout.tsx` to render a tab bar with links to `/admin/users` (User Management) and `/admin/queue` (Queue Jobs). Use the same inline tab pattern as `src/resources/js/pages/dashboard.tsx:100` (border-b-2 active state). The existing `src/resources/js/pages/admin/users/index.tsx` already uses `AdminLayout` so it picks up the nav automatically.

---

## Phase 3: User Story 1 - View Queue Job Status (Priority: P1) MVP

**Goal**: An admin opens `/admin/queue` and sees pending, executing, and failed jobs with type, queue, attempts, and timestamps.

**Independent Test**: Queue several jobs (some that succeed, some that fail), open `/admin/queue`, and confirm all categories appear with correct details.

### Tests for User Story 1

- [X] T003 [P] [US1] Feature tests in `src/tests/Feature/AdminQueueTest.php`: (a) admin sees the queue page with pending/executing/failed sections; (b) non-admin gets 403 on `GET /admin/queue`; (c) unauthenticated redirected to login.

### Implementation for User Story 1

- [X] T004 [P] [US1] Create `src/app/Http/Controllers/AdminQueueController.php` with `index()`: reads pending (`jobs` where `reserved_at IS NULL`), executing (`jobs` where `reserved_at IS NOT NULL`), and failed (`failed_jobs` paginated 10/page). Parses `displayName` from each job's JSON payload for the job type. Does NOT include the raw `payload` in the response. Returns Inertia `admin/queue/index` with `{ pending, executing, failed }` props.
- [X] T005 [US1] Add `Route::get('queue', [AdminQueueController::class, 'index'])->name('queue.index')` inside the `['auth', 'admin']` group in `src/routes/web.php`. Import the controller.
- [X] T006 [US1] Create `src/resources/js/pages/admin/queue/index.tsx`: renders three sections (Pending, Executing, Failed) as tables or lists. Each job row shows: type (short class name), queue name, attempts, created/reserved/failed timestamp. Failed rows show truncated exception (first 200 chars). Uses `AdminLayout`. Auto-refreshes via `router.reload({ only: [...] })` every 10 seconds.
- [X] T007 [US1] Register the new page in the Inertia page resolver (verify `src/resources/js/pages/admin/queue/index.tsx` is picked up by the `import.meta.glob` in the SSR/CSR bootstrap — it should be automatic).

**Checkpoint**: US1 functional — admin sees live queue state.

---

## Phase 4: User Story 2 - Manage Jobs (Priority: P2)

**Goal**: Cancel pending, retry/delete failed, release executing — all from the queue view.

**Independent Test**: Queue a job, cancel it (disappears); fail a job, retry it (re-enters queue); delete a failed job (disappears).

### Tests for User Story 2

- [ ] T008 [P] [US2] Feature tests in `src/tests/Feature/AdminQueueTest.php`: (a) cancel a pending job → deleted from `jobs`; (b) release an executing job → `reserved_at` cleared; (c) retry a failed job → re-queued (appears in `jobs`); (d) delete a failed job → removed from `failed_jobs`; (e) non-admin gets 403 on all action routes.

### Implementation for User Story 2

- [ ] T009 [US2] Add management methods to `src/app/Http/Controllers/AdminQueueController.php`: `cancel($id)` deletes pending job from `jobs` (where `reserved_at IS NULL`); `release($id)` clears `reserved_at` on an executing job; `retry($uuid)` re-dispatches a failed job's payload to its queue then forgets the failed record; `delete($uuid)` forgets the failed record. Each redirects back with a flash message.
- [ ] T010 [US2] Add routes in `src/routes/web.php` under the admin group: `POST queue/{id}/cancel`, `POST queue/{id}/release`, `POST queue/failed/{uuid}/retry`, `POST queue/failed/{uuid}/delete`.
- [ ] T011 [US2] Add action buttons to `src/resources/js/pages/admin/queue/index.tsx`: "Cancel" on pending rows, "Release" on executing rows, "Retry" + "Delete" on failed rows. Each POSTs to the corresponding route and lets the auto-refresh show the result.

**Checkpoint**: US1 + US2 functional — admin can view and manage jobs.

---

## Phase 5: User Story 3 - Recently Completed (Priority: P3, deferrable)

**Goal**: Show recently completed jobs (within a retention window) and auto-prune old ones.

**Independent Test**: Process a job to completion → it appears in "Recently Completed" → advance past retention → it's pruned.

### Implementation for User Story 3

- [ ] T012 [P] [US3] Create migration `src/database/migrations/2026_07_26_000000_create_completed_job_log_table.php`: `id`, `job_type` string(255), `queue` string(255), `completed_at` timestamp (useCurrent), timestamps. Index on `completed_at`.
- [ ] T013 [P] [US3] Create `src/app/Listeners/LogCompletedJob.php`: listens to `Illuminate\Queue\Events\JobProcessed`, inserts a row into `completed_job_log` with `job_type` from `$event->job->resolveName()`, `queue` from `$event->job->getQueue()`, `completed_at` = now.
- [ ] T014 [P] [US3] Create `src/app/Console/Commands/PruneCompletedJobs.php`: deletes `completed_job_log` rows older than the retention window (`config('admin.completed_retention_days', 3)`). Daily scheduled.
- [ ] T015 [US3] Register the listener in `src/app/Providers/AppServiceProvider.php` (or `EventServiceProvider` if present) and schedule the prune command daily in `src/routes/console.php` (or the scheduler). Add `completed_retention_days` to `src/config/services.php` under a new `admin` key.
- [ ] T016 [US3] Add a "Recently Completed" section to `src/app/Http/Controllers/AdminQueueController.php` `index()`: reads `completed_job_log` (latest 20, ordered by `completed_at DESC`) and passes it as a prop. Update `src/resources/js/pages/admin/queue/index.tsx` to render the section.

**Checkpoint**: All three stories functional.

---

## Phase 6: Polish

- [ ] T017 [P] Run PHPStan: `docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html --entrypoint vendor/bin/phpstan podkeep-app:latest analyse --no-progress`; resolve all findings.
- [ ] T018 [P] Run Pint: `docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html --entrypoint vendor/bin/pint podkeep-app:latest --dirty`.
- [ ] T019 [P] Run fallow: `docker run --rm -v /home/nate/src/podkeep:/repo -w /repo/src --entrypoint sh node:22 -c 'git config --global --add safe.directory /repo && ./node_modules/.bin/fallow audit --base main'`.
- [ ] T020 Run the full Pest suite + complete the manual verification steps in `src/specs/018-admin-queue-panel/quickstart.md`.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies.
- **Foundational (Phase 2)**: Admin nav — needed by US1's page.
- **US1 (Phase 3)**: Depends on Phase 2 (nav).
- **US2 (Phase 4)**: Depends on US1 (same controller + page).
- **US3 (Phase 5)**: Independent of US2; depends on US1 (same controller + page) for the view.
- **Polish (Phase 6)**: After desired stories.

### Parallel Opportunities

- T003/T004 (test + controller for US1) — different files.
- T008/T009 (test + controller actions for US2) — different files.
- T012/T013/T014 (P3 migration + listener + command) — all different files.

## Implementation Strategy

### MVP (US1 only)
Phases 1–3. Admin sees pending/executing/failed jobs. Stop and validate.

### Incremental
- Add US2 (management actions) → validate.
- Add US3 (recently completed) → validate.
- Polish.

---

## Notes

- The admin routes are under the existing `['auth', 'admin']` middleware group in `src/routes/web.php`.
- Job payloads contain sensitive data (file paths, API keys) — NEVER include them in the Inertia response. Parse only `displayName` for the job type.
- Failed jobs can accumulate — paginate (10/page).
- Auto-refresh uses `router.reload({ only: [...] })` every 10 seconds (matching the dashboard's existing polling).
- The `AdminLayout` currently is just `<AppLayout>{children}</AppLayout>` — adding a tab bar is the minimal extensible change.
