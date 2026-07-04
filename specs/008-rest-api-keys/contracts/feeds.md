# Contract: Feed Management API

**Feature**: 008-rest-api-keys
**Base URL**: `/api/v1`

## Endpoints

### List Feeds

```
GET /api/v1/feeds
```

Returns all feeds owned by the authenticated user.

**Response** (200):
```json
{
  "data": [
    {
      "id": 1,
      "title": "My Podcast",
      "description": "A show about things",
      "website_url": "https://example.com",
      "is_public": false,
      "slug": "my-podcast",
      "user_guid": "550e8400-e29b-41d4-a716-446655440000",
      "token": "a1b2c3d4e5f6...",
      "items_count": 5,
      "created_at": "2026-06-01T12:00:00.000000Z",
      "updated_at": "2026-06-01T12:00:00.000000Z"
    }
  ]
}
```

The `token` field is included because the authenticated user owns these feeds. The `items_count` is included via `withCount('items')`.

---

### Create Feed

```
POST /api/v1/feeds
```

**Request body** (JSON):

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `title` | string | Yes | max:255 |
| `description` | string | No | max:1000 |
| `website_url` | string | No | url, max:255 |
| `is_public` | boolean | No | default: false |

**Response** (201):
```json
{
  "data": {
    "id": 2,
    "title": "New Show",
    "description": null,
    "website_url": null,
    "is_public": false,
    "slug": "new-show",
    "user_guid": "550e8400-e29b-41d4-a716-446655440001",
    "token": "f7e6d5c4b3a2...",
    "items_count": null,
    "created_at": "2026-07-03T10:00:00.000000Z",
    "updated_at": "2026-07-03T10:00:00.000000Z"
  }
}
```

**Auto-generated**: `slug` (from title, unique per user), `user_guid` (UUID), `token` (64-char random).

---

### Show Feed

```
GET /api/v1/feeds/{id}
```

**Response** (200): Single feed resource (same shape as list item, without `items_count` unless loaded).

**Errors**: 404 if feed does not exist or belongs to another user.

---

### Update Feed

```
PUT /api/v1/feeds/{id}
PATCH /api/v1/feeds/{id}
```

**Request body** (JSON, all fields optional):

| Field | Type | Rules |
|-------|------|-------|
| `title` | string | max:255 |
| `description` | string | max:1000 |
| `website_url` | string | url, max:255 |
| `is_public` | boolean | |

**Response** (200): Updated feed resource.

**Authorization**: `FeedPolicy@update` — must own the feed.

---

### Delete Feed

```
DELETE /api/v1/feeds/{id}
```

**Response** (204): No content.

**Authorization**: `FeedPolicy@delete` — must own the feed.

**Effect**: Feed and its `feed_items` pivot records are deleted (cascade). Library items and media files are NOT deleted — they remain in the user's library.
