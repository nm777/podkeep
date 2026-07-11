# Data Model: Stable Podcast Links

**Feature**: 011-stable-podcast-links
**Date**: 2026-07-10

## Schema Change

**None.** No migration is added or modified. The `feeds` table already has all
required columns. This feature changes only the **write semantics** of one
existing column (`slug`), enforced in application code.

## Affected Entity: `Feed` (the "podcast")

Table: `feeds` — defined in
`src/database/migrations/2025_07_14_011051_create_feeds_table.php`.

Model: `src/app/Models/Feed.php`.

| Field | Type | Null | Notes |
|---|---|---|---|
| `id` | bigint (PK) | no | Auto-increment. Used for internal/admin routes and the RSS cache key. |
| `user_id` | foreignId → `users` | no | Owner. Cascade-delete. |
| `title` | string | no | **Display name — freely editable.** Shown in RSS `<title>`, share page, dashboard. |
| `description` | text | yes | Editable. |
| `cover_image_url` | string | yes | Editable. |
| `is_public` | boolean | default false | Editable. Governs whether `?token=` is required on public URLs. |
| `slug` | string | no | **WRITE-ONCE — set at creation, immutable thereafter.** Public URL segment. |
| `user_guid` | uuid | no | **Immutable.** Set once at creation. Public URL segment. |
| `token` | string(64), unique | yes | **Immutable.** Set once at creation. Access token for private feeds. |
| `episode_order` | enum (`EpisodeOrderType`) | no | Editable. |
| `website_url` | string | yes | Editable (added by later migration). |

**Constraint**: `unique(['user_id', 'slug'])` — slug uniqueness scoped per-user.
Because `slug` no longer changes after creation, this constraint cannot be
violated by a rename; collisions are only possible at creation and are already
handled by `generateUniqueSlug()`.

## State Transition: `slug`

```
 [absent] --create()--> [SET from title via generateUniqueSlug()] --> [IMMUTABLE for life of feed]
                                |
                                v
                  (never written again by update() on any path)
```

- **Creation** (`FeedController::store`, `Api/V1/FeedController::store`): `slug`
  is derived from `title` via `Str::slug()`, with per-user collision suffixing
  (`-1`, `-2`, …). Unchanged.
- **Update** (both web and API): `slug` is **not written**. Before this feature,
  the web `update()` overwrote it; that line is removed. The API `update()`
  already did not write it.

## Invariants Enforced by This Feature

1. **Slug immutability**: once a `Feed` row exists with a `slug`, no `update()`
   call on any controller path mutates it.
2. **Title independence**: `title` may change freely; `slug` does not.
3. **Public-URL permanence**: the public URL `{user_guid}/{slug}` resolves for
   the lifetime of the feed regardless of title changes, because neither segment
   changes after creation.
4. **Ownership**: only `feed.user_id` may trigger an update (enforced by
   `FeedPolicy::update` and `Auth::user()->feeds()->findOrFail()`). Pre-existing;
   not changed.

## Validation Rules (relevant)

Source: `src/app/Http/Requests/FeedRequest.php` (web),
`src/app/Http/Requests/Api/V1/UpdateFeedRequest.php` (API).

- `title`: required (web) / sometimes+nullable (API), string, max 255. Editable.
- **`slug`**: not accepted in any request input. It is never mass-assigned from
  user input on either path — it is only ever set programmatically at creation.

No validation rule changes are required. The `slug` field is simply no longer
written during updates.
