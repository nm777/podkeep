# Contract: Library Item (Media) Management API

**Feature**: 008-rest-api-keys
**Base URL**: `/api/v1`

## Endpoints

### List Library Items

```
GET /api/v1/library
```

Returns all library items owned by the authenticated user with their processing status and media file details.

**Response** (200):
```json
{
  "data": [
    {
      "id": 10,
      "title": "Episode 1",
      "description": "First episode",
      "source_type": "upload",
      "source_url": null,
      "published_at": "2026-07-01T00:00:00.000000Z",
      "is_duplicate": false,
      "processing_status": "completed",
      "processing_status_display": "Completed",
      "is_processing": false,
      "is_pending": false,
      "has_completed": true,
      "has_failed": false,
      "processing_error": null,
      "media_file": {
        "id": 5,
        "public_url": "/files/media/abc123.mp3",
        "file_hash": "sha256...",
        "mime_type": "audio/mpeg",
        "filesize": 52428800,
        "duration": null,
        "source_url": null,
        "created_at": "2026-07-01T10:00:00.000000Z",
        "updated_at": "2026-07-01T10:00:00.000000Z"
      },
      "created_at": "2026-07-01T10:00:00.000000Z",
      "updated_at": "2026-07-01T10:00:00.000000Z"
    }
  ]
}
```

---

### Upload Media File

```
POST /api/v1/library
Content-Type: multipart/form-data
```

**Request** (multipart form data):

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `title` | string | Yes | max:255 |
| `description` | string | No | max:1000 |
| `file` | file | Yes | mimes:mp3,mp4,m4a,wav,ogg, max:512000 KB (~500MB) |
| `feed_ids` | array | No | Each must be a feed id owned by the user |
| `published_at` | date | No | |

**Response** (201):
```json
{
  "data": {
    "id": 11,
    "title": "Episode 2",
    "description": null,
    "source_type": "upload",
    "source_url": null,
    "published_at": null,
    "is_duplicate": false,
    "processing_status": "pending",
    "processing_status_display": "Pending",
    "is_processing": false,
    "is_pending": true,
    "has_completed": false,
    "has_failed": false,
    "processing_error": null,
    "media_file": null,
    "created_at": "2026-07-03T10:00:00.000000Z",
    "updated_at": "2026-07-03T10:00:00.000000Z"
  },
  "message": "File uploaded. Processing will complete shortly."
}
```

The `media_file` is null initially — it is populated once async processing completes. Clients poll `GET /api/v1/library/{id}` to check `processing_status`.

---

### Add Media via URL

```
POST /api/v1/library
Content-Type: application/json
```

**Request body** (JSON):

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `title` | string | Yes | max:255 |
| `description` | string | No | max:1000 |
| `url` | string | Yes (for direct media URL) | url, max:2048, must end in .mp3/.mp4/.m4a/.wav/.ogg |
| `source_url` | string | Yes (for YouTube) | url, max:2048 |
| `feed_ids` | array | No | |
| `published_at` | date | No | |

Note: Provide exactly one of `file`, `url`, or `source_url` (mutually exclusive).

**Response** (201): Same shape as upload response with `processing_status: "pending"`.

---

### Show Library Item

```
GET /api/v1/library/{id}
```

**Response** (200): Single library item resource (same shape as list item).

**Errors**: 404 if item does not exist or belongs to another user.

---

### Update Library Item

```
PUT /api/v1/library/{id}
PATCH /api/v1/library/{id}
```

**Request body** (JSON, all optional):

| Field | Type | Rules |
|-------|------|-------|
| `title` | string | max:255 |
| `description` | string | max:1000 |
| `published_at` | date | |

**Response** (200): Updated library item resource.

**Authorization**: `LibraryItemPolicy@update` — must own the item.

---

### Delete Library Item

```
DELETE /api/v1/library/{id}
```

**Response** (204): No content.

**Authorization**: `LibraryItemPolicy@delete` — must own the item.

**Effect**: Library item is deleted. If the associated `MediaFile` has no remaining library items referencing it, the media file record and physical file on disk are also deleted.
