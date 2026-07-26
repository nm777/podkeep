# Implementation Plan: Admin Queue Job Panel

**Branch**: `018-admin-queue-panel` | **Date**: 2026-07-26 | **Spec**: [spec.md](spec.md)

## Summary

An admin-only Queue Jobs panel showing pending, executing, and failed jobs from the database queue, with management actions (cancel pending, retry/delete failed, release executing). Extends the existing `AdminLayout` with a shared nav (User Management | Queue Jobs) for extensibility. P3 adds a lightweight completion log for recently completed jobs.

## Technical Context

**Language/Version**: PHP 8.4 (Laravel 13), TypeScript (React 19+)
**Primary Dependencies**: Laravel 13, Inertia.js v3, Tailwind CSS v4, Pest PHP v4
**Storage**: PostgreSQL (production) / SQLite (tests); existing `jobs` + `failed_jobs` tables; new `completed_job_log` table (P3)
**Testing**: Pest feature tests; admin access control + queue actions
**Constraints**: Admin-only (`['auth', 'admin']` middleware); no raw payload exposure; paginated failed jobs; extensible admin nav

## Constitution Check

- [x] **API-First**: Backend controller + routes land first; Inertia page consumes. Same pattern as UserManagement.
- [x] **Media Processing**: N/A — reads queue state, does not process media.
- [x] **Test-Driven**: Pest tests for access control (non-admin denied), queue reading, and each management action.
- [x] **Feed Standards**: N/A.
- [x] **Security**: `['auth', 'admin']` middleware on all routes; payloads not exposed; DB queries scoped.
- [x] **Performance**: Failed-jobs list paginated; queries indexed by existing `failed_at`.

## Project Structure

```text
src/
├── app/
│   ├── Http/Controllers/
│   │   └── AdminQueueController.php          # NEW — queue view + actions
│   ├── Console/Commands/
│   │   └── PruneCompletedJobs.php            # NEW (P3) — prune old completed_job_log
│   └── Listeners/
│       └── LogCompletedJob.php               # NEW (P3) — JobProcessed event listener
├── database/migrations/
│   └── ..._create_completed_job_log_table.php # NEW (P3)
├── resources/js/
│   ├── layouts/admin-layout.tsx              # MODIFY — add nav (User Mgmt | Queue Jobs)
│   └── pages/admin/queue/
│       └── index.tsx                         # NEW — queue jobs dashboard
├── routes/web.php                            # MODIFY — admin queue routes
└── tests/Feature/AdminQueueTest.php          # NEW
```
