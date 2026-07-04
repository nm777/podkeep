# Implementation Plan: Per-Feed Episode Ordering

**Branch**: `009-feed-episode-order` | **Date**: 2026-07-04 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/009-feed-episode-order/spec.md`

## Summary

Add a per-feed `episode_order` field (enum: `newest_first` default, `chronological`) that controls the direction episodes are sorted across all display surfaces (RSS feed, share player, feed edit page). The existing `sequence` column on `feed_items` already stores manual ordering from drag-and-drop — this feature simply controls whether that sequence is read ascending (chronological, for audiobooks) or descending (newest first, for podcasts). Also fixes a latent bug where the RSS feed ignores sequence entirely and outputs items in DB insertion order.

## Technical Context

**Language/Version**: PHP 8.4 (Laravel 12), TypeScript (React 19)
**Primary Dependencies**: Laravel Framework, Inertia.js v2, Tailwind CSS v4, Pest PHP v3, Laravel Sanctum v4
**Storage**: MySQL 8.0+ (SQLite for tests), database-backed queues, local `public` disk for media files
**Testing**: Pest PHP v3 (backend), feature tests required
**Target Platform**: Web application with Docker containerization
**Project Type**: Web application (session-based UI + REST API)
**Performance Goals**: RSS generation <5s, API responses <500ms
**Constraints**: 90% test coverage, RSS 2.0 compliance, must not break existing feed behavior

**Key existing assets**:
- `sequence` column already exists on `feed_items` pivot table — stores manual ordering from drag-and-drop
- Drag-and-drop reordering already works in `resources/js/pages/feeds/edit.tsx` via `useFeedItemReorder` hook
- `ShareController` already sorts by `sequence` ascending
- `ProcessingStatusType` enum pattern in `app/Enums/` — template for new `EpisodeOrderType` enum
- `AddLibraryItemToFeedsJob` assigns `max(sequence) + 1` to new items — already correct for both order modes

**Unknowns resolved in Phase 0**:
- Episode order model: enum values and semantics (resolved → `newest_first` = sequence DESC, `chronological` = sequence ASC)
- pubDate handling for podcast clients (resolved → document ordering by sequence, existing pubDate values used as-is)
- Auto-append behavior (resolved → existing `max(sequence) + 1` already correct for both modes)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **API-First**: `episode_order` field is exposed via the existing API `StoreFeedRequest`/`UpdateFeedRequest`/`FeedResource` before any frontend changes. No new API endpoints needed — the existing feed CRUD endpoints gain a new field.
- [x] **Media Processing**: No media processing changes — this feature only affects ordering/display.
- [x] **Test-Driven**: Feature tests written first for migration, enum, RSS ordering, share player ordering, feed edit loading order, and API field exposure.
- [x] **Feed Standards**: RSS 2.0 compliance maintained — the `<item>` elements are reordered by sequence but each item's structure is unchanged. RSS caching is cleared on order change.
- [x] **Security**: `episode_order` validated via enum rule in form requests. Feed ownership enforced by existing policies.
- [x] **Performance**: No performance impact — sorting is at the query level (`orderBy('sequence', $direction)`) with existing indexes. RSS cache cleared on change.

## Project Structure

### Documentation (this feature)

```text
specs/009-feed-episode-order/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
└── tasks.md             # Phase 2 output (/speckit.tasks - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
src/
├── app/
│   ├── Enums/
│   │   └── EpisodeOrderType.php           # NEW — enum: NewestFirst, Chronological
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── FeedController.php         # EDIT — store/update episode_order, edit loads by sequence
│   │   │   ├── RssController.php          # EDIT — order items by sequence + direction
│   │   │   ├── ShareController.php        # EDIT — sort by direction, not just ascending
│   │   │   └── Api/V1/FeedController.php  # EDIT — store episode_order in create()
│   │   ├── Requests/
│   │   │   ├── FeedRequest.php            # EDIT — add episode_order validation
│   │   │   └── Api/V1/
│   │   │       ├── StoreFeedRequest.php   # EDIT — add episode_order rule
│   │   │       └── UpdateFeedRequest.php  # EDIT — add episode_order rule
│   │   └── Resources/
│   │       └── FeedResource.php           # EDIT — expose episode_order field
│   └── Models/
│       └── Feed.php                       # EDIT — add episode_order to fillable + casts + items() ordering
├── database/
│   └── migrations/
│       └── *_add_episode_order_to_feeds_table.php  # NEW
└── resources/
    ├── js/
    │   ├── components/
    │   │   └── feed-form-fields.tsx       # EDIT — add episode_order select
    │   ├── pages/
    │   │   └── feeds/
    │   │       └── edit.tsx               # EDIT — include episode_order in useForm
    │   └── types/
    │       └── index.d.ts                 # EDIT — add episode_order to Feed type
    └── views/
        └── rss.blade.php                  # Already iterates $feed->items — fixed by model ordering

tests/
└── Feature/
    ├── EpisodeOrderTest.php               # NEW — RSS ordering, share player, edit page, API
    └── FeedManagementTest.php             # EXISTING — verify no regressions
```

**Structure Decision**: This is a focused feature with no new directories, controllers, or services. It adds one migration, one enum, and modifies existing files across the stack (model, controllers, form requests, resources, views, frontend components). The `Feed::items()` relationship gains a default `orderBy('sequence')` so all callers automatically respect ordering — then the direction is applied at each rendering surface based on the feed's `episode_order`.
