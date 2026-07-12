# Quick Start: Feed Type Ordering

**Feature**: 012-feed-type-ordering  
**Date**: 2026-07-12

## What This Feature Adds

Two podcast feed types replace the old Episode Order setting:
- **Static** — Fixed chapter content (audiobooks, lecture series). Manual drag-and-drop + bulk quick-sort. Sequence-derived pubDates for reliable ordering in podcast apps.
- **Append** — Ongoing content (podcasts, serials). New episodes auto-promoted to top. Optional display date in episode descriptions.

## Key Files to Touch

### Backend
- `app/Enums/FeedType.php` — **New**: enum replacing `EpisodeOrderType`
- `app/Models/Feed.php` — Replace `episode_order` cast with `feed_type` → `FeedType`
- `app/Models/LibraryItem.php` — Add `display_date` to fillable + casts
- `app/Http/Controllers/FeedController.php` — Feed type in create/update/edit; quick-sort aware `syncFeedItems`
- `app/Http/Controllers/RssController.php` — Feed-type-aware query direction + pubDate strategy
- `app/Http/Controllers/ShareController.php` — Feed-type-aware item ordering
- `app/Http/Requests/FeedRequest.php` — Validate `feed_type` instead of `episode_order`
- `resources/views/rss.blade.php` — pubDate by feed type; display_date in description
- `database/migrations/` — Rename `episode_order` → `feed_type` + add `display_date`

### Frontend
- `resources/js/components/feed-form-fields.tsx` — Replace episode_order dropdown with feed type selector
- `resources/js/pages/feeds/edit.tsx` — Quick-sort buttons for Static feeds; display_date field for Append
- `resources/js/types/index.d.ts` — Update `Feed` type with `feed_type`; add `display_date` to `LibraryItem`

### Tests
- `tests/Feature/FeedItemSyncTest.php` — Update for feed_type
- `tests/Feature/FeedEditTest.php` — Update for feed_type + redirect
- `tests/Feature/EpisodeOrderTest.php` — Rename/refactor for feed_type
- `tests/Feature/StableFeedLinksTest.php` — Update redirect assertions
- New: quick-sort tests, append feed pubDate tests, display_date tests

## Migration Notes

- Existing `episode_order: chronological` feeds → `feed_type: static`
- Existing `episode_order: newest_first` feeds → `feed_type: append`
- The `EpisodeOrderType` enum is deleted; replaced by `FeedType`
- `library_items.display_date` is nullable — existing items have `null`

## Testing Quick Start

```bash
# Run feed-type-specific tests
php artisan test --filter=FeedType
php artisan test --filter=FeedItemSync
php artisan test tests/Feature/RssFeedTest.php
```
