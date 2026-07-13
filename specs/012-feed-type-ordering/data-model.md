# Data Model: Feed Type Ordering

**Feature**: 012-feed-type-ordering  
**Date**: 2026-07-12

## Entity Changes

### Feed (modified)

| Field | Type | Change | Description |
|-------|------|--------|-------------|
| `feed_type` | enum(string) | **Renamed** from `episode_order` | `static` or `append`. Determines ordering strategy and available tools. |
| `episode_order` | — | **Removed** | Replaced by `feed_type` |

**Enum: `FeedType`** (replaces `EpisodeOrderType`):
- `Static = 'static'` — Fixed chapter-based content. Manual ordering with quick-sort. Sequence-derived pubDates.
- `Append = 'append'` — Ongoing content. Auto-ordered newest-first. Creation-timestamp pubDates.

**Migration mapping**:
- `episode_order = 'chronological'` → `feed_type = 'static'`
- `episode_order = 'newest_first'` → `feed_type = 'append'`

### LibraryItem (modified)

| Field | Type | Change | Description |
|-------|------|--------|-------------|
| `display_date` | date (nullable) | **Added** | Optional date for Append feed episodes. Appears in RSS description prefix when set. |

### FeedItem (unchanged)

| Field | Type | Description |
|-------|------|-------------|
| `feed_id` | FK | Belongs to Feed |
| `library_item_id` | FK | Belongs to LibraryItem |
| `sequence` | integer | Position in the feed. Lower = earlier in arrangement. |
| `created_at` | timestamp | When the item was added to the feed. Used as pubDate for Append feeds. |

## State Transitions

### Feed Type Switching

```
Static ──→ Append:  Items re-ordered by created_at DESC. Sequences reassigned.
Append ──→ Static:  Existing sequences preserved (or assigned by current order if missing).
```

## pubDate Derivation

| Feed Type | pubDate Source | Spacing |
|-----------|---------------|---------|
| Static | `feed.created_at + (sequence × 1 minute)` | 1 minute between episodes |
| Append | `feed_item.created_at` | Real timestamps |

## RSS Description Format (Append with display_date)

```
[July 4, 2026] {original_description}
```

If no display_date set, description is unchanged.

## Relationships

```
Feed (1) ──< FeedItem (>0) >── (1) LibraryItem
  │                                      │
  └─ feed_type: static|append            └─ display_date: nullable (Append feeds)
```
