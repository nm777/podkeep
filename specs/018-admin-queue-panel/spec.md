# Feature Specification: Admin Queue Job Panel

**Feature Branch**: `018-admin-queue-panel`  
**Created**: 2026-07-26  
**Status**: Draft  
**Input**: User description: "I want an admin panel where I can see all queue jobs and their statuses. After a few days I don't care about completed ones, but I'd like to see currently executing ones, failed jobs, and recently completed ones. I don't know how much control Laravel offers, but being able to cancel an executing or pending job may also be useful. The admin panel should be only accessible to administrators and should be constructed in such a way that it supports adding other useful administrative tools and tasks later. For example tabs or some kind of nav that supports other pages."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - View Queue Job Status (Priority: P1)

An administrator wants to see at a glance what the background workers are doing: which jobs are waiting, which are currently running, which have failed, and which recently completed. They open the admin panel and navigate to the Queue Jobs view, where they see a live table of jobs grouped by status — pending, executing, failed, and recently completed — with enough detail (job type, queue name, when it started, attempts, error message for failures) to understand the system's health without SSH-ing into a container.

**Why this priority**: This is the core visibility the user asked for — without it, the admin is flying blind on background operations (especially long-running chapter transcription). Everything else (management actions, extensibility) builds on this view.

**Independent Test**: Can be tested by queuing several jobs (some that succeed, some that fail), then opening the admin Queue Jobs view and confirming all four statuses appear with correct details — without using any management actions.

**Acceptance Scenarios**:

1. **Given** the admin is signed in and has the admin role, **When** they open the admin panel, **Then** they see a navigation element (tabs or sidebar) that includes a "Queue Jobs" entry alongside the existing "User Management" entry.
2. **Given** there are pending, executing, failed, and recently completed jobs, **When** the admin opens the Queue Jobs view, **Then** all four categories are visible, each with job type, queue name, and relevant timestamps.
3. **Given** a failed job exists, **When** the admin views it, **Then** they see the failure reason (exception message or summary).
4. **Given** a non-admin user tries to access the admin panel, **When** they navigate to the admin URL, **Then** they are denied access.
5. **Given** the admin panel's navigation, **When** the admin looks at it, **Then** it is structured so new admin pages can be added later without restructuring.

---

### User Story 2 - Manage Jobs (Priority: P2)

An administrator sees a job stuck in "executing" or "pending" state, or a failed job they want to retry or clean up. From the Queue Jobs view they can: cancel (remove) a pending job so it never runs; retry a failed job to give it another attempt; or delete a failed job to clean up the list. For currently executing jobs, they can clear the job's reservation so it is re-queued (the system cannot hard-kill the running process, but releasing the reservation lets the job be re-attempted or abandoned).

**Why this priority**: Management actions are valuable once visibility is in place; they address the "stuck job" pain the user has already encountered (e.g., chapter generation stuck at 'processing').

**Independent Test**: Can be tested by queuing a pending job, cancelling it from the panel, and confirming it no longer appears in the queue; and by failing a job, retrying it, and confirming it re-enters the queue — without needing the recently-completed or retention features.

**Acceptance Scenarios**:

1. **Given** a pending job in the queue, **When** the admin clicks "Cancel," **Then** the job is removed from the queue and no longer appears.
2. **Given** a failed job, **When** the admin clicks "Retry," **Then** the job is re-queued for processing.
3. **Given** a failed job, **When** the admin clicks "Delete," **Then** the job is permanently removed from the failed-jobs list.
4. **Given** a job currently executing (reserved), **When** the admin clicks "Release," **Then** the job's reservation is cleared so it becomes available for re-processing (or abandonment) on the next worker cycle.
5. **Given** any management action, **When** it completes, **Then** the job list refreshes to reflect the change.

---

### User Story 3 - Recently Completed Job Visibility with Retention (Priority: P3)

An administrator wants to confirm that recently-submitted jobs actually finished successfully (not just that they're no longer "pending"). The panel shows jobs that completed within the last few days, along with when they finished. After the retention window (configurable, defaulting to a few days), completed records are automatically pruned so the list stays manageable.

**Why this priority**: The database queue driver does not retain completed jobs by default, so this requires extra plumbing (a completion log). It's valuable for auditing but not blocking the core visibility.

**Independent Test**: Can be tested by processing a job to completion, confirming it appears in the "recently completed" list with a timestamp, advancing past the retention window, and confirming it is pruned — without needing the management actions.

**Acceptance Scenarios**:

1. **Given** a job has recently completed, **When** the admin views the Queue Jobs panel, **Then** the job appears under "Recently Completed" with its completion time.
2. **Given** a completed job older than the retention window, **When** the retention cleanup runs, **Then** the record is removed automatically.
3. **Given** the admin wants to verify a specific job completed, **When** they look at the "Recently Completed" section, **Then** they can see the job type and when it finished.

---

### Edge Cases

- **No jobs in a category**: if there are no pending, executing, failed, or recently completed jobs, the panel shows an empty state for that category (not an error).
- **Very large number of failed jobs**: the failed-jobs list should be paginated or limited, not loaded all at once.
- **Job payload contains sensitive data**: the admin view should not expose raw job payloads (which may contain API keys, file paths, etc.) — only metadata (type, queue, status, timestamps, error).
- **Concurrent management actions**: if two admins act on the same job simultaneously, the action should be idempotent (retrying an already-retried job, deleting an already-deleted job) without errors.
- **Executing job finishes while admin views it**: the panel should refresh on a reasonable interval or on action, not require a manual page reload to see status changes.
- **Non-admin accidentally discovers the URL**: access is denied at the middleware/route level, not just hidden in the UI.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The admin panel MUST be accessible only to users with the admin role, enforced at the route/middleware level (not just in the UI).
- **FR-002**: The admin panel MUST use a navigation structure (tabs or sidebar) that includes the existing User Management page and the new Queue Jobs page, designed so additional admin pages can be added without restructuring.
- **FR-003**: The Queue Jobs view MUST show jobs in four categories: pending (waiting in queue), executing (reserved by a worker), failed (in the failed-jobs table), and recently completed (within the retention window).
- **FR-004**: Each job entry MUST display: job type/display name, queue name, attempts count, creation/reservation/completion timestamps (as applicable), and for failed jobs, the failure reason.
- **FR-005**: The admin MUST be able to cancel (permanently remove) a pending job from the queue.
- **FR-006**: The admin MUST be able to retry a failed job (re-queue it for processing).
- **FR-007**: The admin MUST be able to delete a failed job from the failed-jobs list.
- **FR-008**: The admin MUST be able to release an executing (reserved) job's reservation so it becomes available for re-processing.
- **FR-009**: Job management actions (cancel, retry, delete, release) MUST refresh the job list to reflect the change.
- **FR-010**: The panel MUST NOT expose raw job payloads (which may contain sensitive data) — only metadata.
- **FR-011**: Recently completed jobs MUST be retained for a configurable period (defaulting to a few days) and automatically pruned thereafter.
- **FR-012**: The failed-jobs list MUST be paginated or limited to prevent loading an excessive number at once.
- **FR-013**: The Queue Jobs view SHOULD auto-refresh (or provide a manual refresh button) so the admin sees current status without a full page reload.

### Key Entities *(include if feature involves data)*

- **Queue Job** *(existing system data)*: A background task in the processing pipeline. Has a type (e.g., transcription, segmentation, media processing), a queue name (e.g., default, chapters), a status (pending, executing, failed, completed), attempt count, timestamps, and on failure, an error reason. The panel reads this data; it does not create new entities.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: An admin can open the Queue Jobs panel and see the count and details of pending, executing, and failed jobs within 2 seconds.
- **SC-002**: An admin can cancel a pending job, retry a failed job, or delete a failed job with a single click, with the list reflecting the change immediately.
- **SC-003**: Non-admin users are denied access to the admin panel at the route level (0 unauthorized accesses succeed).
- **SC-004**: The admin navigation supports adding a new admin page by adding one nav entry — no restructuring of existing pages required.
- **SC-005**: Recently completed jobs are visible for the retention window (default a few days) and automatically pruned thereafter (the list does not grow unbounded).
- **SC-006**: The panel does not expose raw job payloads or sensitive data embedded in job parameters.

## Assumptions

- The panel extends the existing admin section (`/admin/users`) with a shared admin navigation; the existing User Management page becomes one tab/entry in that nav.
- "Cancel" for a pending job means removing it from the queue so it never executes. "Release" for an executing job means clearing its reservation so it is re-queued or abandoned on the next worker cycle — hard-killing a running process is not feasible from the web layer.
- The database queue driver does not retain completed jobs by default; the "recently completed" view requires a lightweight completion log (e.g., recording job type + completion time in a small table, pruned by a scheduled task). This is scoped as P3.
- The admin role is already defined (`is_admin` on the User model, already used by the User Management page and its middleware).
- Auto-refresh of the job list uses a poll interval (e.g., every 10 seconds) or a manual refresh button; real-time push updates are out of scope.
- The panel shows job metadata only (type, queue, status, timestamps, error) — not raw payloads (which may contain file paths, API keys, etc.).
