# RSS Feed Contract: Feed Type Ordering

**Feature**: 012-feed-type-ordering  
**Date**: 2026-07-12

## RSS Output by Feed Type

### Static Feeds

- **Item order**: by `sequence` ASC (user's arrangement, chapter 1 first)
- **pubDate**: `feed.created_at + (sequence × 1 minute)` — deterministic, 1-minute spacing
- **Description**: unchanged (no display date)
- **Purpose**: Fixed chapter content where user controls order via drag-and-drop/quick-sort

```xml
<!-- Static feed: chapter 1 has earliest pubDate -->
<item>
  <title>01 - The Departure of Boromir</title>
  <pubDate>Sat, 04 Jul 26 00:01:00 +0000</pubDate>  <!-- feed.created_at + 1 min -->
  ...
</item>
<item>
  <title>21 - The Choices of Master Samwise</title>
  <pubDate>Sat, 04 Jul 26 00:21:00 +0000</pubDate>  <!-- feed.created_at + 21 min -->
  ...
</item>
```

### Append Feeds

- **Item order**: by `feed_item.created_at` DESC (newest addition first)
- **pubDate**: `feed_item.created_at` — actual addition timestamp
- **Description**: if `display_date` is set, prepended as `[Date] original_description`
- **Purpose**: Ongoing content where new episodes surface immediately

```xml
<!-- Append feed: newest addition appears first -->
<item>
  <title>Episode 42: Latest News</title>
  <description>[July 12, 2026] Today we discuss...</description>
  <pubDate>Sat, 12 Jul 26 14:30:00 +0000</pubDate>  <!-- when added to feed -->
  ...
</item>
<item>
  <title>Episode 41: Previous Topic</title>
  <description>Yesterday we covered...</description>  <!-- no display_date set -->
  <pubDate>Fri, 11 Jul 26 09:00:00 +0000</pubDate>
  ...
</item>
```

## Cache Invalidation

The RSS cache (`rss.{feed_id}`) MUST be cleared when:
- Feed type is changed
- Episodes are added/removed/reordered
- Display date is set/changed on an episode
- Feed type switch occurs

## Web API Endpoints (existing, modified)

### PUT `/feeds/{feed}` — Update Feed

**Request** (modified):
```json
{
  "title": "...",
  "description": "...",
  "is_public": false,
  "feed_type": "static",
  "items": [
    { "library_item_id": 1, "sequence": 0 },
    { "library_item_id": 2, "sequence": 1 }
  ]
}
```

**Response**: Redirect to `feeds.edit` (unchanged)

### GET `/feeds/{feed}/edit` — Edit Page

**Response** (modified):
```json
{
  "feed": {
    "id": 1,
    "feed_type": "static",
    "items": [/* loaded in type-appropriate direction */]
  }
}
```

**Item loading direction**:
- Static: `sequence` ASC (user's arrangement)
- Append: `created_at` DESC (newest first)
