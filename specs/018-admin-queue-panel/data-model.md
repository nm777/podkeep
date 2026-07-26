# Data Model: Admin Queue Job Panel

## Existing tables (read-only)

### `jobs` (Laravel queue)
- `id`, `queue`, `payload` (JSON with `displayName`, `data`), `attempts`, `reserved_at` (null=pending, set=executing), `available_at`, `created_at`.

### `failed_jobs` (Laravel failed queue)
- `id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`.

## New table (P3)

### `completed_job_log`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | auto |
| `job_type` | string(255) | from `JobProcessed` event's `job->resolveName()` |
| `queue` | string(255) | queue name |
| `completed_at` | timestamp | when the job finished |
| `created_at` | timestamp | for ordering |

No relationships; a flat append-only log pruned by a scheduled command.

## Migration (P3)

```php
Schema::create('completed_job_log', function (Blueprint $table) {
    $table->id();
    $table->string('job_type', 255);
    $table->string('queue', 255);
    $table->timestamp('completed_at')->useCurrent();
    $table->timestamps();
    $table->index('completed_at');
});
```
