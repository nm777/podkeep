# Phase 0 Research: Admin Queue Job Panel

## R1 — Admin nav/layout

**Decision**: Extend the existing `AdminLayout` (`resources/js/layouts/admin-layout.tsx`) with a horizontal tab bar linking to `/admin/users` and `/admin/queue`. New admin pages add one `<Link>` to the tab bar — no restructuring.

**Rationale**: `AdminLayout` currently wraps `AppLayout` with no nav. Adding a simple tab row (matching the dashboard's tab pattern) is the minimal extensible change. The existing User Management page already uses `<AdminLayout>`.

## R2 — Reading queue state

**Decision**: Read directly from the `jobs` and `failed_jobs` tables via `DB` facade (no Eloquent models for queue tables — they're Laravel's schema). Pending = `reserved_at IS NULL`; executing = `reserved_at IS NOT NULL`. Parse the job type from the JSON `payload`'s `displayName` field. Do NOT expose the full payload (security).

**Rationale**: Laravel's `jobs`/`failed_jobs` tables are internal schema; reading via `DB::table()` is the standard approach. The `displayName` in the payload gives a human-readable job type without exposing parameters.

## R3 — Management actions

**Decision**:
- **Cancel pending**: `DB::table('jobs')->where('id', $id)->whereNull('reserved_at')->delete();`
- **Release executing**: `DB::table('jobs')->where('id', $id)->update(['reserved_at' => null]);` (clears the reservation; the job becomes available for re-processing on the next worker cycle).
- **Retry failed**: `app('queue.failer')->forget($uuid)` after re-dispatching the payload — or use Laravel's `Queue::retry([$uuid])`.
- **Delete failed**: `app('queue.failer')->forget($uuid)`.

**Rationale**: The database queue stores jobs in `jobs`; direct DB operations are the cleanest control. Laravel's queue failer (`DatabaseFailedJobProvider`) provides `forget()`/`all()`. For retry, the simplest reliable approach is re-dispatching the failed job's payload to its original queue, then forgetting the failed record.

## R4 — Recently completed jobs (P3)

**Decision**: Listen to `Illuminate\Queue\Events\JobProcessed` and write a row to a small `completed_job_log` table (`job_type`, `queue`, `completed_at`). A daily scheduled command prunes rows older than the retention window (configurable, default 3 days). The admin panel reads this table for the "Recently Completed" section.

**Rationale**: The database queue driver deletes completed jobs immediately. The only way to show them is to log on completion. The listener + prune command is the standard pattern (Laravel Horizon does this internally; we replicate the minimum).

## R5 — Auto-refresh

**Decision**: The Queue Jobs page uses Inertia's `router.reload({ only: ['...] })` on a 10-second interval (matching the dashboard's existing polling pattern) while the page is visible.

**Rationale**: Polling is the simplest reliable approach (no WebSocket infrastructure). 10s matches the dashboard's existing interval. Only the relevant props reload (not a full page reload).
