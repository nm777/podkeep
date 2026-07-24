# Contract: Feed Data & Form Payload

**Branch**: `015-library-item-management` | **Date**: 2026-07-24

This is a Laravel + Inertia application. The user-facing "interfaces" are (a) the data the server sends to Inertia pages as props, (b) the form payloads the client posts back, and (c) the TypeScript types mirroring the backend. This file covers the Feed entity, its validation payload, and the TS type. Page-level props are in [page-contracts.md](page-contracts.md).

## Feed entity (server shape)

`feeds` table after this feature. Only the last row is new.

| Column | Type | Default | Source of truth |
|---|---|---|---|
| `id` | bigint PK | auto | `feeds.id` |
| `user_id` | bigint FK→users | — | owner |
| `title` | string | — | required |
| `description` | text nullable | null | optional |
| `website_url` | string nullable | null | optional |
| `cover_image_url` | string nullable | null | optional |
| `is_public` | boolean | false | RSS/public visibility |
| `is_hidden_from_selector` | boolean | **false** | **NEW** — omit from add-media selector when true |
| `feed_type` | enum(static, append) | append | ordering semantics |
| `slug` | string | — | unique per user |
| `user_guid` | uuid | — | stable feed id for RSS |
| `token` | string unique nullable | null | private media access |
| `created_at`, `updated_at` | timestamps | — | — |

## Feed form payload (client → server)

Routes (existing, unchanged): `POST /feeds` (`feeds.store`) and `PUT/PATCH /feeds/{feed}` (`feeds.update`), both validated by `App\Http\Requests\FeedRequest`.

**Added validation rule:**

```php
'is_hidden_from_selector' => ['boolean'],
```

**Full payload shape sent by the feed create/edit forms** (`FeedFormFields`):

| Field | Type | Required | Validation |
|---|---|---|---|
| `title` | string | yes | `required, string, max:255` |
| `description` | string | no | `nullable, string, max:1000` |
| `website_url` | string | no | `nullable, string, url, max:255` |
| `is_public` | boolean | no | `boolean` |
| `is_hidden_from_selector` | boolean | no | `boolean` — **NEW** |
| `feed_type` | enum | no | `nullable, Rule::enum(FeedType::class)` |
| `items` | array | no | `nullable, array` (edit only) |
| `items.*.library_item_id` | int | yes (per item) | `required, integer, exists:library_items,id` + must belong to the authenticated user |
| `items.*.sequence` | int | yes (per item) | `required, integer, min:0` |
| `display_dates` | map<int, date> | no | `nullable, array` (append feeds only) |

**Authorization:** `Gate::authorize('update', $feed)` on update; create is scoped to `Auth::user()->feeds()`. No new policy.

## FeedType enum (unchanged)

`App\Enums\FeedType`, values `static`, `append`. Not modified by this feature.

## TypeScript `Feed` type (client shape)

`resources/js/types/index.d.ts` — one field added:

```ts
export interface Feed {
    id: number;
    title: string;
    description?: string;
    website_url?: string;
    is_public: boolean;
    is_hidden_from_selector: boolean;   // NEW
    feed_type: 'static' | 'append';
    slug: string;
    user_guid: string;
    token?: string;
    items_count?: number;
    items?: FeedItem[];
    created_at: string;
    updated_at: string;
}
```

The shared Inertia `feeds` prop (see [page-contracts.md](page-contracts.md)) serializes each feed including `is_hidden_from_selector`, so the client can filter without an extra request.
