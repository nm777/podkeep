# Implementation Plan: Improved Library Item Management

**Branch**: `015-library-item-management` | **Date**: 2026-07-24 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/015-library-item-management/spec.md`

## Summary

Two independent improvements to how library items are managed:
1. **Feed editor media picker (P1):** Replace the tiny, non-searchable "Add Library Items" box on the feed edit page with a two-tab layout ("Feed Items" / "Add Media"). The "Add Media" tab is a tall, scrollable, client-side searchable list (filter by title, mirroring the existing feeds-list search). The backend stops capping `userLibraryItems` at 100 so the full personal library is searchable.
2. **Hide feeds from the add-media selector (P2):** Add a per-feed `is_hidden_from_selector` boolean (default false = shown), editable on the feed form. The dashboard filters hidden feeds out of the `MediaUploadButton`/`FeedSelector` only; the dashboard feed list and RSS are unaffected.

## Technical Context

**Language/Version**: PHP 8.4 (Laravel 13), TypeScript (React 19+)
**Primary Dependencies**: Laravel Framework 13, Inertia.js v2, Tailwind CSS v4, Pest PHP v4, @inertiajs/react v2
**Storage**: PostgreSQL (production) / SQLite (tests); local `public` disk for media (unchanged by this feature)
**Testing**: Pest PHP feature tests (backend), existing Inertia page behavior; TDD per constitution
**Target Platform**: Web application (Docker)
**Project Type**: Web application (Laravel + Inertia/React) — server-rendered Inertia pages, no separate REST API consumed here
**Performance Goals**: Feed edit page load <500ms (single user-scoped query, eager-loaded); client-side search is O(n) over a modest personal library
**Constraints**: Follow existing FeedController/FeedRequest/FeedFormFields conventions; reuse `SearchInput` + `useDebouncedValue`; keep RSS output untouched; authorization via existing `Gate::authorize('update', $feed)` and ownership rules
**Scale/Scope**: Single-user-scoped queries; personal libraries are modest so loading the full library client-side is acceptable (ceiling noted in research.md)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **API-First**: Backend changes (migration → Feed model → FeedRequest → FeedController) land before/with frontend. The "Add Media" picker is a pure frontend restructure of existing data; no new endpoint needed. The hide-from-selector flag is persisted via the existing `FeedController@store/update` Inertia form posts. ✓
- [x] **Media Processing**: N/A — this feature touches no upload/processing pipeline. No new queued jobs. ✓
- [x] **Test-Driven**: Feature tests written first (FeedRequest validation, controller persistence of the flag, edit page receives full library, hidden feeds excluded from picker). ✓
- [x] **Feed Standards**: RSS output is explicitly unchanged. Hiding a feed from the selector must not affect RSS/dash/share — covered by an explicit test. ✓
- [x] **Security**: New field validated as boolean in `FeedRequest`; existing `Gate::authorize('update', $feed)` guards mutation; existing per-user ownership check on `items.*.library_item_id` retained. ✓
- [x] **Performance**: Edit page query already eager-loads `items.libraryItem.mediaFile`; `userLibraryItems` eager-loads `mediaFile`. Removing the `limit(100)` adds one unbounded user-scoped SELECT — acceptable for personal libraries; ceiling documented. ✓

No violations. No complexity tracking entries required.

## Project Structure

### Documentation (this feature)

```text
specs/015-library-item-management/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   ├── feed-data-contract.md
│   └── page-contracts.md
└── tasks.md             # Phase 2 output (/speckit.tasks — not created here)
```

### Source Code (repository root)

```text
src/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── FeedController.php        # edit(): drop limit(100); store/update(): persist flag
│   │   │   └── LibraryController.php     # (unchanged — feed_ids flow untouched)
│   │   └── Requests/
│   │       └── FeedRequest.php           # add 'is_hidden_from_selector' => ['boolean']
│   ├── Models/
│   │   └── Feed.php                      # fillable + boolean cast
│   └── Enums/ (unchanged)
├── database/
│   ├── migrations/
│   │   └── 2026_07_24_000001_add_is_hidden_from_selector_to_feeds_table.php
│   └── factories/  (FeedFactory updated if it sets attributes)
├── resources/js/
│   ├── components/
│   │   ├── feed-form-fields.tsx          # add "Show in Add Media list" checkbox
│   │   ├── feed-selector.tsx             # (no change — receives pre-filtered feeds)
│   │   └── (reuse SearchInput, useDebouncedValue)
│   ├── pages/
│   │   ├── feeds/edit.tsx                # tabbed Feed Items / Add Media; searchable Add Media
│   │   └── dashboard.tsx                 # filter hidden feeds before passing to MediaUploadButton
│   └── types/index.d.ts                  # Feed.is_hidden_from_selector
└── tests/
    ├── Feature/
    │   ├── FeedManagementTest.php (or new HideFeedFromSelectorTest.php)
    │   └── FeedEditPageTest.php (full library + hidden flag in props)
    └── Pest.php
```

**Structure Decision**: Pure addition to the existing Laravel + Inertia structure. No new directories, services, or jobs. One migration, one model field, one request rule, two controller tweaks, and focused frontend edits to `feeds/edit.tsx`, `feed-form-fields.tsx`, `dashboard.tsx`, and the `Feed` type. All UI reuses existing `SearchInput`/`useDebouncedValue` components.

## Complexity Tracking

> No constitution violations. Table intentionally empty.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| — | — | — |
