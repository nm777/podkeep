# Implementation Plan: Feed Type Ordering

**Branch**: `012-feed-type-ordering` | **Date**: 2026-07-12 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/012-feed-type-ordering/spec.md`

## Summary

Replace the single `episode_order` sort-direction setting with two distinct feed types: **Static** (chapter-based, manually ordered, sequence-derived pubDates) and **Append** (ongoing, newest-first, addition-timestamp pubDates). Add bulk quick-sort actions (alphabetical, reverse-alphabetical, chronological, reverse-chronological) for Static feeds. Add optional display date for Append feed episode descriptions.

## Technical Context

**Language/Version**: PHP 8.4 (Laravel 13), TypeScript (React 19+)  
**Primary Dependencies**: Laravel Framework 13, Inertia.js v3, Tailwind CSS v4, Pest PHP v4  
**Storage**: PostgreSQL (production), SQLite (tests), file storage for media  
**Testing**: Pest PHP v4 (backend), feature tests required  
**Target Platform**: Web application with Docker containerization  
**Project Type**: Web application (backend API + React frontend via Inertia)  
**Performance Goals**: Feed generation <5s, API responses <500ms  
**Constraints**: Must follow Laravel conventions, RSS 2.0 compliance, existing test suite must pass  
**Scale/Scope**: Feed type migration, RSS pubDate strategy, frontend quick-sort UI

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **API-First**: Backend (FeedType enum, migration, controller, RSS template) before frontend (edit page UI)
- [x] **Media Processing**: No media processing changes in this feature
- [x] **Test-Driven**: Tests planned for feed type selection, quick-sort, pubDate strategy, migration
- [x] **Feed Standards**: RSS 2.0 compliant pubDate strategy for both feed types
- [x] **Security**: Existing authorization (policies, Gate) preserved; validation updated for feed_type
- [x] **Performance**: RSS cache clearing preserved; sequence-based pubDates are deterministic

## Project Structure

### Documentation (this feature)

```text
specs/012-feed-type-ordering/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/
│   └── rss-feed.md      # RSS output contract per feed type
└── tasks.md             # Phase 2 output (not yet created)
```

### Source Code (repository root)

```text
src/
├── app/
│   ├── Enums/
│   │   └── FeedType.php              # NEW: replaces EpisodeOrderType
│   ├── Http/Controllers/
│   │   ├── FeedController.php        # Modified: feed_type CRUD, quick-sort sync
│   │   ├── RssController.php         # Modified: feed-type-aware direction + pubDate
│   │   └── ShareController.php       # Modified: feed-type-aware ordering
│   ├── Http/Requests/
│   │   └── FeedRequest.php           # Modified: validate feed_type
│   └── Models/
│       ├── Feed.php                  # Modified: feed_type cast
│       └── LibraryItem.php           # Modified: display_date field
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   └── feed-form-fields.tsx  # Modified: feed type selector
│   │   ├── pages/
│   │   │   └── feeds/edit.tsx        # Modified: quick-sort buttons, display_date
│   │   └── types/index.d.ts         # Modified: feed_type, display_date
│   └── views/
│       └── rss.blade.php             # Modified: feed-type-aware pubDate + display_date
├── database/
│   └── migrations/
│       └── 2026_07_12_rename_episode_order_to_feed_type.php  # NEW
└── tests/
    ├── Feature/
    │   ├── FeedItemSyncTest.php      # Modified: feed_type
    │   ├── FeedEditTest.php          # Modified: feed_type + redirects
    │   ├── EpisodeOrderTest.php      # Modified → FeedTypeTest.php
    │   └── StableFeedLinksTest.php   # Modified: redirect assertions
    └── Unit/
        └── (FeedType enum tests if needed)
```

**Structure Decision**: Follows existing Laravel + Inertia conventions. New enum, migration, and modified controllers/views. No new directories or structural changes.

## Complexity Tracking

No constitution violations. All principles satisfied.
