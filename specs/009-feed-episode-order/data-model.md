# Phase 1: Data Model — Per-Feed Episode Ordering

**Feature**: 009-feed-episode-order
**Date**: 2026-07-04

## New Entity: EpisodeOrderType Enum

### Migration: `add_episode_order_to_feeds_table`

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| `episode_order` | string(20) | No | `'newest_first'` | Enum values: `newest_first`, `chronological` |

**Migration approach**: Add column with default value, then backfill all existing feeds to `'newest_first'` (same as the default — no behavioral change for existing feeds).

```php
Schema::table('feeds', function (Blueprint $table) {
    $table->string('episode_order', 20)->default('newest_first')->after('is_public');
});
```

No index needed — `episode_order` is used for display logic, not for querying/filtering feeds.

### Enum: `App\Enums\EpisodeOrderType`

```php
enum EpisodeOrderType: string
{
    case NewestFirst = 'newest_first';
    case Chronological = 'chronological';

    public function getDisplayName(): string;
    public function isNewestFirst(): bool;
    public function isChronological(): bool;
}
```

- Keys are TitleCase per boost guidelines
- Values are lowercase snake_case for database storage
- `getDisplayName()`: returns `'Newest First'` / `'Chronological'`
- Follows the exact pattern of `ProcessingStatusType`

### Validation rules for `episode_order`:

- In all form requests: `['nullable', 'string', Rule::enum(EpisodeOrderType::class)]`
- Default when not provided: `EpisodeOrderType::NewestFirst`

---

## Existing Entity Changes

### Feed Model (`App\Models\Feed`)

**`$fillable`**: Add `'episode_order'` to the existing array.

**`casts()` method** (NEW — currently Feed has no casts): Create a `casts()` method adding:
```php
'episode_order' => EpisodeOrderType::class,
```

**`items()` relationship**: Change from bare `hasMany` to include default ordering:
```php
public function items()
{
    return $this->hasMany(FeedItem::class)->orderBy('sequence');
}
```

This ensures all callers (RssController, FeedController::edit, ShareController, API controllers) automatically get sequence-ordered items without needing to add `orderBy` at each call site. The direction (ASC vs DESC) is applied at rendering time.

---

## Rendering Surface Changes

### RSS Feed (`RssController` + `rss.blade.php`)

**Current**: Loads items with no ordering (insertion order). Iterates `$feed->items` directly in Blade.

**After**: The `Feed::items()` relationship now defaults to `orderBy('sequence')`. For direction control:
- Load the feed with items ordered by sequence in the feed's chosen direction
- ASC for chronological, DESC for newest_first

```php
$direction = $feed->episode_order->isChronological() ? 'asc' : 'desc';
$feed->load(['items' => fn ($q) => $q->orderBy('sequence', $direction), 'items.libraryItem.mediaFile']);
```

The Blade template requires **no changes** — it already iterates `$feed->items`.

### Share Player (`ShareController`)

**Current**: `$feed->items->sortBy('sequence')` (always ascending).

**After**: Respect feed direction:
```php
$items = $feed->episode_order->isChronological()
    ? $feed->items->sortBy('sequence')
    : $feed->items->sortByDesc('sequence');
```

Note: `sortBy` returns a new collection (doesn't mutate the original), so this is safe.

### Feed Edit Page (`FeedController::edit`)

**Current**: `$feed->load(['items.libraryItem', 'items.libraryItem.mediaFile'])` — no ordering.

**After**: The `Feed::items()` relationship now defaults to `orderBy('sequence')`, so the edit page automatically loads items in sequence order. No explicit change needed in the controller — the relationship default handles it.

However, the edit page should always load ASCENDING (so sequence 0 is first in the list) regardless of the feed's display mode, because the drag-and-drop hook assigns `sequence = array_index` starting at 0. If items load DESC, the hook's 0-based indexing would reverse the order on save.

**Decision**: Override the relationship default in the edit controller to always load ASC:
```php
$feed->load(['items' => fn ($q) => $q->orderBy('sequence', 'asc'), 'items.libraryItem', 'items.libraryItem.mediaFile']);
```

---

## API Changes

### FeedResource

Add `'episode_order' => $this->episode_order` to the resource output. The cast returns the enum instance; the resource should output the `.value` or `.name` for JSON serialization. Since Laravel's model casting returns the enum instance, and `JsonResource` serializes it, the output will be the string value (e.g., `"newest_first"`).

### API Form Requests

Add `'episode_order' => ['nullable', 'string', Rule::enum(EpisodeOrderType::class)]` to:
- `StoreFeedRequest` (API V1)
- `UpdateFeedRequest` (API V1)

### API FeedController

In `store()`: Add `'episode_order' => $validated['episode_order'] ?? EpisodeOrderType::NewestFirst` to the `create()` array.

In `update()`: Mass assignment via `$feed->update($validated)` handles it automatically once the field is in `$fillable` and validated.

---

## Entity Relationship (unchanged)

```
Feed
 ├─ episode_order: enum (NEW)
 └─hasMany(ordered by sequence)─→ FeedItem (pivot: sequence)
                                   └─belongsTo─→ LibraryItem
                                                   └─belongsTo─→ MediaFile
```

## Data Integrity Notes

- **No schema changes to `feed_items`** — the `sequence` column already exists and works.
- **Backward compatible** — all existing feeds default to `'newest_first'`, preserving current behavior.
- **Sequence value consistency**: `AddLibraryItemToFeedsJob` assigns `max(sequence) + 1` (starts at 1), while `FeedController::syncFeedItems` uses array index (starts at 0). This inconsistency exists today and is not introduced by this feature. The ordering works correctly regardless of starting index because all comparisons are relative within the same feed.
- **RSS cache**: All order changes clear the feed's RSS cache via `Cache::forget("rss.{$feed->id}")` — already handled by the existing `update` method.
