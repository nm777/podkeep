# Quickstart: Improved Library Item Management

**Branch**: `015-library-item-management` | **Spec**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

A dev-oriented snapshot of what changes, where, and how to verify it. Read this before opening `/speckit.tasks`.

## What you're building

1. **Hide feeds from the add-media picker** — new `feeds.is_hidden_from_selector` boolean (default false = shown). The dashboard filters it out of `MediaUploadButton` only; dashboard list + RSS untouched.
2. **Tabbed, searchable media picker on the feed edit page** — replace the tiny `max-h-48` "Add Library Items" box with two tabs ("Feed Items" / "Add Media"); the Add Media tab is tall and searches by title, and the backend stops capping the library at 100.

## Files touched

**Backend (do first):**
- `database/migrations/2026_07_24_000001_add_is_hidden_from_selector_to_feeds_table.php` — new, additive boolean.
- `app/Models/Feed.php` — `fillable` + `casts()`.
- `app/Http/Requests/FeedRequest.php` — `'is_hidden_from_selector' => ['boolean']`.
- `app/Http/Controllers/FeedController.php` — `store()`/`update()` persist the flag; `edit()` drops `->limit(100)` on `userLibraryItems`.
- `database/factories/FeedFactory.php` — add `is_hidden_from_selector => false` if missing.

**Frontend:**
- `resources/js/types/index.d.ts` — `Feed.is_hidden_from_selector: boolean`.
- `resources/js/components/feed-form-fields.tsx` — "Show in Add Media list" checkbox.
- `resources/js/pages/dashboard.tsx` — `feeds.filter((f) => !f.is_hidden_from_selector)` into `MediaUploadButton`.
- `resources/js/pages/feeds/edit.tsx` — two-tab layout + searchable tall "Add Media" list (reuse `SearchInput` + `useDebouncedValue`).

## Reuse, don't rebuild

- `SearchInput` + `useDebouncedValue` — already power the dashboard feeds search and the feed-items search; use them for the Add Media tab.
- `LibraryItemInfo` (`feeds/edit.tsx`) — already renders title + duration/size/"Processing…"; reuse for the Add Media rows.
- Tab UI — mirror the existing inline tab pattern in `dashboard.tsx:100` (border-b-2 active state). No shared Tabs component exists; don't add a dependency for one consumer.
- `FeedSelector` — leave it dumb; it renders whatever feeds it's given.

## How to run the tooling (ephemeral containers — see AGENTS.md)

```bash
# Pest (tests use SQLite :memory:, no DB container needed)
docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html \
  --entrypoint php podkeep-app:latest artisan test --compact \
  tests/Feature/FeedManagementTest.php

# PHPStan
docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html \
  --entrypoint vendor/bin/phpstan podkeep-app:latest analyse --no-progress

# Pint (changed files)
docker run --rm -v /home/nate/src/podkeep/src:/var/www/html -w /var/www/html \
  --entrypoint vendor/bin/pint podkeep-app:latest --dirty
```

Frontend build is produced at Docker image build time (`npm run build` in the Dockerfile `frontend` stage); the user rebuilds/redeploys. JS lint (fallow) runs on the host or transiently if needed.

## Verify by hand

1. Create a feed, edit it, uncheck "Show in Add Media list", save → reopen Add Media dialog → that feed is gone from the selector; it's still on the dashboard; its RSS still lists its items.
2. On any feed's edit page → "Add Media" tab → type a partial title → list filters live; list is tall/scrollable; items already on the feed aren't shown; `+` adds them and they move to "Feed Items".
3. Re-check "Show in Add Media list" → feed reappears in the picker.

## Test coverage targets (constitution: TDD, 90%)

- `is_hidden_from_selector` defaults false; can be set true via store/update; persisted.
- Hidden feed excluded from the `MediaUploadButton`/`FeedSelector` feed list (assert via the prop/dashboard render path used by existing tests).
- Hidden feed still appears in dashboard feed list; RSS unchanged; existing memberships intact.
- `FeedController::edit` returns the full `userLibraryItems` (no 100-cap) with `mediaFile` loaded.
- `FeedRequest` accepts `is_hidden_from_selector` as boolean; rejects non-boolean.
