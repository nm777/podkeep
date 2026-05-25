# Data Model: Podcast Share Player Page

## Overview

No new database tables, migrations, or models are required. This feature reuses the existing data model entirely.

## Existing Entities Used

### Feed (read-only access)

| Field | Type | Usage in Share Page |
|-------|------|-------------------|
| `id` | int | Internal reference |
| `user_guid` | uuid | URL segment for routing |
| `slug` | string | URL segment for routing |
| `title` | string | Display as feed title |
| `description` | text (nullable) | Display as feed description |
| `cover_image_url` | string (nullable) | Display as feed artwork |
| `is_public` | boolean | Access control: true = open, false = requires token |
| `token` | string (64 chars, nullable) | Validated against `?token=` query param for private feeds |

### FeedItem (read-only access, ordered by sequence)

| Field | Type | Usage in Share Page |
|-------|------|-------------------|
| `id` | int | Internal reference |
| `feed_id` | int | Belongs to Feed |
| `library_item_id` | int | Links to LibraryItem |
| `sequence` | int | Sort order for episode list |

### LibraryItem (read-only access, via FeedItem relationship)

| Field | Type | Usage in Share Page |
|-------|------|-------------------|
| `id` | int | Internal reference |
| `title` | string | Episode title |
| `description` | text (nullable) | Episode description |
| `published_at` | date (nullable) | Episode publish date |
| `processing_status` | enum | Filter: only show COMPLETED items with media |

### MediaFile (read-only access, via LibraryItem relationship)

| Field | Type | Usage in Share Page |
|-------|------|-------------------|
| `id` | int | Internal reference |
| `file_path` | string | Constructs media URL: `/files/{file_path}` |
| `mime_type` | string | Content-Type for audio element |
| `duration` | float (nullable) | Display episode duration |
| `filesize` | int (nullable) | Display file size |

## Data Flow

```
Request: GET /share/{user_guid}/{feed_slug}[?token=xxx]
  |
  v
ShareController::show()
  |-> Feed::where('user_guid', $user_guid)
  |       ->where('slug', $feed_slug)
  |       ->with(['items.libraryItem.mediaFile'])
  |       ->first()
  |
  |-> Access check: is_public || token matches
  |
  |-> Filter items: only completed items with media files
  |
  v
Inertia::render('share/show', {
    feed: { title, description, cover_image_url },
    items: [{ title, description, published_at, duration, media_url }],
    isPublic: bool,
    token: string|null (only if private feed with valid token)
})
```

## Data Passed to Frontend

### Feed Props

```typescript
interface ShareFeed {
    title: string;
    description: string | null;
    cover_image_url: string | null;
}
```

### Episode Props

```typescript
interface ShareEpisode {
    id: number;
    title: string;
    description: string | null;
    published_at: string | null;
    duration: number | null;
    media_url: string;
}
```

The `media_url` is constructed server-side:
- Public feeds: `/files/{file_path}`
- Private feeds: `/files/{file_path}?feed_token={token}`

## Validation Rules

- No form inputs on this page — validation is purely access control
- Token validation: string comparison against `Feed.token` (64 chars, alphanumeric)
- 404 returned for: feed not found, private feed without token, private feed with wrong token
- Episodes without completed media files are excluded from the list
