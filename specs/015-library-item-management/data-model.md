# Data Model: Improved Library Item Management

**Branch**: `015-library-item-management` | **Date**: 2026-07-24

This feature adds **one column** to an existing entity. No new tables, no new relationships, no migrations of foreign keys. `LibraryItem` and `FeedItem` are structurally unchanged.

## Entities

### Feed (modified)

The `feeds` table gains a single boolean controlling whether the feed is offered in the add-media feed-selection list.

**Existing columns (unchanged):**

| Column | Type | Notes |
|---|---|---|
| `id` | bigint (PK) | |
| `user_id` | bigint (FK → users) | cascade on delete |
| `title` | string | |
| `description` | text, nullable | |
| `cover_image_url` | string, nullable | |
| `is_public` | boolean, default false | RSS/public visibility — untouched |
| `feed_type` | enum(static, append), default append | ordering semantics — untouched |
| `slug` | string | unique per user |
| `user_guid` | uuid | |
| `token` | string, unique, nullable | private-feed media access |
| `created_at` / `updated_at` | timestamps | |

**New column:**

| Column | Type | Default | Nullable | Notes |
|---|---|---|---|---|
| `is_hidden_from_selector` | boolean | `false` (0) | NO | `false` = feed **appears** in the add-media selector (shown). `true` = feed is **omitted** from the selector only. Does **not** affect dashboard listing, RSS output, sharing, or existing feed memberships. |

**Validation rules** (`FeedRequest`, added):

- `is_hidden_from_selector` → `['boolean']`

**Model changes** (`Feed`):

- Add `is_hidden_from_selector` to `$fillable`.
- Add to `casts()` as `'is_hidden_from_selector' => 'boolean'`.

**State transitions:** None. A simple toggle between `false` (shown) and `true` (hidden), set by the feed owner from the feed form. No lifecycle, no cascading effects.

**Ownership/authorization:** Mutation is scoped to `Auth::user()->feeds()` (create) and `Gate::authorize('update', $feed)` (update) — already enforced in `FeedController`. No new policy.

### LibraryItem (unchanged)

Listed and searched in the feed editor's new "Add Media" tab. No structural change. The only behavioral change is upstream: `FeedController::edit` stops applying `limit(100)` so the full personal library is passed to the page as `userLibraryItems` for client-side search.

### FeedItem (unchanged)

Join between Feed and LibraryItem. Untouched by this feature.

## Migration

A single additive migration: `2026_07_24_000001_add_is_hidden_from_selector_to_feeds_table.php`

```php
Schema::table('feeds', function (Blueprint $table) {
    $table->boolean('is_hidden_from_selector')->default(false)->after('is_public');
});
```

- Additive only → no data loss, no downtime.
- Default `false` satisfies FR-003 (every existing feed remains "shown" automatically; no backfill needed).

## TypeScript Types

`resources/js/types/index.d.ts` — `Feed` interface gains one field:

```ts
export interface Feed {
    // ...existing fields...
    is_hidden_from_selector: boolean;
    // ...
}
```

This mirrors the DB column and is what `dashboard.tsx` filters on before passing feeds to `MediaUploadButton`.
