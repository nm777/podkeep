# Quickstart: Admin Queue Job Panel

**Branch**: `018-admin-queue-panel` | **Spec**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

## What you're building

1. **P1**: Admin-only Queue Jobs view (pending, executing, failed) with admin nav (extensible tabs).
2. **P2**: Management actions — cancel pending, retry/delete failed, release executing.
3. **P3**: Recently completed log (event listener + prune command).

## Files touched

**Backend:**
- `app/Http/Controllers/AdminQueueController.php` — new: reads `jobs`/`failed_jobs` tables, management actions.
- `routes/web.php` — add admin queue routes under `['auth', 'admin']`.
- (P3) `app/Listeners/LogCompletedJob.php` + `app/Console/Commands/PruneCompletedJobs.php` + migration.

**Frontend:**
- `resources/js/layouts/admin-layout.tsx` — add tab nav (User Mgmt | Queue Jobs).
- `resources/js/pages/admin/queue/index.tsx` — new: queue jobs dashboard with 4 sections.

## How to run the tooling

```bash
docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html \
  --entrypoint php podkeep-app:latest artisan test --compact tests/Feature/AdminQueueTest.php
docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html \
  --entrypoint vendor/bin/phpstan podkeep-app:latest analyse --no-progress
docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html \
  --entrypoint vendor/bin/pint podkeep-app:latest --dirty
```

## Verify by hand

1. Sign in as an admin → see the admin nav with "Queue Jobs" tab.
2. Queue a job → it appears under "Pending" or "Executing".
3. Cancel a pending job → it disappears.
4. Fail a job → it appears under "Failed" with the error.
5. Retry a failed job → it re-enters the queue.
6. Sign in as non-admin → `/admin/queue` returns 403.
