# Contract: Feed Item Management API

**Feature**: 008-rest-api-keys
**Base URL**: `/api/v1`

## Overview

Feed items are the pivot between feeds and library items — they define which episodes belong to which podcast and in what order.

## Endpoints

### List Feed Items

```
GET /api/v1/feeds/{feedId}/items
```

Returns all items attached to a feed, ordered by `sequence`.

**Response** (200):
```json
{
  "data": [
    {
      "id": 1,
      "feed_id": 1,
      "library_item_id": 10,
      "sequence": 0,
      "library_item": {
        "id": 10,
        "title": "Episode 1",
        "description": "First episode",
        "processing_status": "completed",
        "..."
      }
    }
  ]
}
```

**Authorization**: Must own the feed (implicit — feed lookup is scoped to user).

---

### Attach Library Item to Feed

```
POST /api/v1/feeds/{feedId}/items
```

**Request body** (JSON):

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `library_item_id` | int | Yes | Must exist and belong to the authenticated user |
| `sequence` | int | No | min:0; defaults to next available sequence (end of feed) |

**Response** (201):
```json
{
  "data": {
    "id": 5,
    "feed_id": 1,
    "library_item_id": 10,
    "sequence": 3,
    "library_item": { "..." }
  }
}
```

**Authorization**: New `FeedItemPolicy@attach` — must own both the feed and the library item.

**Edge case**: If the library item is still processing (`hasCompleted` is false), the attachment is deferred via `AddLibraryItemToFeedsJob` which fires when processing completes. The endpoint returns 201 immediately with the feed item record; the item appears in RSS only after processing completes.

---

### Update Feed Item (Reorder)

```
PATCH /api/v1/feeds/{feedId}/items/{itemId}
```

**Request body** (JSON):

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `sequence` | int | Yes | min:0 |

**Response** (200): Updated feed item resource.

**Authorization**: Must own the parent feed.

**Note**: When reordering, it is recommended to send the full desired order via the reorder endpoint below rather than patching individual sequences, to avoid conflicts.

---

### Reorder Feed Items

```
PUT /api/v1/feeds/{feedId}/items/reorder
```

**Request body** (JSON):

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `items` | array | Yes | Array of `{id, sequence}` |
| `items.*.id` | int | Yes | Feed item id belonging to this feed |
| `items.*.sequence` | int | Yes | min:0, new position |

**Example request**:
```json
{
  "items": [
    {"id": 5, "sequence": 0},
    {"id": 3, "sequence": 1},
    {"id": 4, "sequence": 2}
  ]
}
```

**Response** (200):
```json
{
  "data": [
    {"id": 5, "feed_id": 1, "library_item_id": 12, "sequence": 0, "...": "..."},
    {"id": 3, "feed_id": 1, "library_item_id": 10, "sequence": 1, "...": "..."},
    {"id": 4, "feed_id": 1, "library_item_id": 11, "sequence": 2, "...": "..."}
  ]
}
```

**Authorization**: Must own the parent feed.

**Backend behavior**: Executes within a database transaction. Updates all provided sequences, then compacts (removes gaps) the ordering. Clears the feed's RSS cache.

---

### Remove Library Item from Feed

```
DELETE /api/v1/feeds/{feedId}/items/{itemId}
```

**Response** (204): No content.

**Authorization**: Must own the parent feed.

**Effect**: The `feed_items` pivot record is deleted. The library item and media file are NOT deleted — they remain in the user's library. RSS cache for the feed is cleared.
