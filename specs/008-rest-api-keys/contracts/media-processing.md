# Contract: Media Processing Operations API

**Feature**: 008-rest-api-keys
**Base URL**: `/api/v1`

## Overview

These endpoints allow API clients to recover from failed media processing and trigger redownloads — mirroring the retry and redownload buttons in the UI.

## Endpoints

### Retry Failed Processing

```
POST /api/v1/library/{id}/retry
```

Re-queues media processing for a library item in a failed state.

**Request body**: None (empty body).

**Response** (200):
```json
{
  "data": {
    "id": 11,
    "title": "Episode 2",
    "processing_status": "pending",
    "processing_status_display": "Pending",
    "is_processing": false,
    "is_pending": true,
    "has_completed": false,
    "has_failed": false,
    "processing_error": null,
    "..."
  },
  "message": "Processing has been restarted."
}
```

**Authorization**: `LibraryItemPolicy@retry` — must own the item.

**Precondition**: The library item must be in a `failed` processing state.

**Error** (422 or 400):
```json
{
  "message": "Only failed items can be retried."
}
```

Returned when the item is not in a failed state.

**Backend behavior**: Resets `processing_status` to `pending`, clears `processing_error`, re-dispatches the processing job (`ProcessMediaFile` or `ProcessYouTubeAudio` depending on source type).

---

### Redownload from Source

```
POST /api/v1/library/{id}/redownload
```

Re-downloads media from the library item's original source URL and replaces the stored file.

**Request body**: None (empty body).

**Response** (200):
```json
{
  "data": {
    "id": 11,
    "title": "Episode 2",
    "processing_status": "processing",
    "processing_status_display": "Processing",
    "is_processing": true,
    "is_pending": false,
    "has_completed": false,
    "has_failed": false,
    "processing_error": null,
    "..."
  },
  "message": "Media file is being redownloaded."
}
```

**Authorization**: `LibraryItemPolicy@update` — must own the item.

**Preconditions**:
- The library item must have an associated `MediaFile`.
- The `MediaFile` must have a `source_url`.

**Errors**:

No media file (404 or 422):
```json
{
  "message": "No media file associated with this library item."
}
```

No source URL (404 or 422):
```json
{
  "message": "Cannot redownload: no source URL available for this media file."
}
```

**Backend behavior**: Sets `processing_status` to `processing`, dispatches `RedownloadMediaFile` job (or `ProcessYouTubeAudio` for YouTube sources). The job downloads from `source_url`, validates the file, and replaces the existing media file (updating hash, path, size, mime type on the `MediaFile` record). Old file is deleted if hash changed.

---

## Rate Limiting

Both endpoints are covered by the global `throttle:api` (60 req/min). No additional per-endpoint throttling is applied, matching the web UI's behavior.
