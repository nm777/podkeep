# Contract: Admin Queue Routes & Props

## Routes (all under `['auth', 'admin']` → `/admin`)

| Method | Path | Action | Purpose |
|---|---|---|---|
| GET | `/admin/queue` | `index` | View pending, executing, failed, recently completed jobs |
| POST | `/admin/queue/{id}/cancel` | `cancel` | Delete a pending job from `jobs` |
| POST | `/admin/queue/{id}/release` | `release` | Clear reservation on an executing job |
| POST | `/admin/queue/failed/{uuid}/retry` | `retry` | Re-dispatch a failed job |
| POST | `/admin/queue/failed/{uuid}/delete` | `delete` | Forget a failed job |

## Inertia Props (`admin/queue/index`)

```json
{
  "pending": [{"id":1,"type":"App\\Jobs\\TranscribeMediaFile","queue":"chapters","attempts":0,"created_at":"..."}],
  "executing": [{"id":2,"type":"App\\Jobs\\SegmentTranscriptIntoChapters","queue":"chapters","attempts":1,"reserved_at":"..."}],
  "failed": [{"id":3,"uuid":"...","type":"App\\Jobs\\TranscribeMediaFile","queue":"chapters","failed_at":"...","exception":"..."}],
  "recentlyCompleted": [{"id":1,"job_type":"App\\Jobs\\ProcessMediaFile","queue":"default","completed_at":"..."}]
}
```

- `payload` is NEVER included in the response (security).
- `type` is parsed from the payload's `displayName`.
- `exception` is truncated to 500 chars.
- `failed` is paginated (10 per page).

## Admin Nav (AdminLayout)

The `AdminLayout` renders a tab bar with links to:
- `/admin/users` — User Management
- `/admin/queue` — Queue Jobs

New admin pages add one `<Link>` entry. No restructuring needed.
