# Contract: Share Endpoint

## Route

```
GET /share/{user_guid}/{feed_slug}
```

## Middleware

- `throttle:120,1` — Rate limited to 120 requests/minute (same as RSS route)
- No authentication middleware required
- No CSRF concern (read-only GET)

## Request

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_guid` | string (UUID) | Yes | Feed owner's unique identifier |
| `feed_slug` | string | Yes | URL-friendly feed name |

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `token` | string | Conditional | Required for private feeds (`is_public = false`). Must match `Feed.token`. |

## Response: Success (200)

Returns an Inertia.js rendered page with the following props:

```typescript
interface SharePageProps {
    feed: {
        title: string;
        description: string | null;
        cover_image_url: string | null;
    };
    episodes: Array<{
        id: number;
        title: string;
        description: string | null;
        published_at: string | null;
        duration: number | null;
        media_url: string;
    }>;
    rssUrl: string;
    isPublic: boolean;
}
```

### Field Details

- `episodes[].media_url`: Full URL to audio file. For private feeds, includes `?feed_token={token}`.
- `episodes[].duration`: Duration in seconds (nullable if unknown).
- `rssUrl`: Absolute URL to RSS feed (with token for private feeds).
- Episodes are ordered by `feed_items.sequence` ascending.
- Only episodes with `processing_status = 'completed'` and a linked `MediaFile` are included.

## Response: Not Found (404)

Returned when:
- No feed matches the `user_guid` + `feed_slug` combination
- Feed is private and no `token` parameter provided
- Feed is private and `token` does not match

Response: Standard Laravel 404 page.

## Examples

### Public Feed

```
GET /share/550e8400-e29b-41d4-a716-446655440000/my-podcast
```

```json
{
    "feed": {
        "title": "My Podcast",
        "description": "A podcast about things",
        "cover_image_url": "https://example.com/cover.jpg"
    },
    "episodes": [
        {
            "id": 1,
            "title": "Episode 1: Hello World",
            "description": "First episode",
            "published_at": "2025-07-14",
            "duration": 3600,
            "media_url": "/files/media/abc123.mp3"
        }
    ],
    "rssUrl": "https://example.com/rss/550e8400-e29b-41d4-a716-446655440000/my-podcast",
    "isPublic": true
}
```

### Private Feed

```
GET /share/550e8400-e29b-41d4-a716-446655440000/my-podcast?token=abc123...def456
```

```json
{
    "feed": {
        "title": "My Private Podcast",
        "description": "Invite only",
        "cover_image_url": null
    },
    "episodes": [
        {
            "id": 2,
            "title": "Secret Episode",
            "description": null,
            "published_at": null,
            "duration": 1800,
            "media_url": "/files/media/xyz789.mp3?feed_token=abc123...def456"
        }
    ],
    "rssUrl": "https://example.com/rss/550e8400-e29b-41d4-a716-446655440000/my-podcast?token=abc123...def456",
    "isPublic": false
}
```
