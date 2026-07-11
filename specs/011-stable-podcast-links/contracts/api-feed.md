# Contract: API v1 Feed Update

**Feature**: 011-stable-podcast-links

## Endpoint

`PUT/PATCH /api/v1/feeds/{id}` — `Api\V1/FeedController::update`
(`src/app/Http/Controllers/Api/V1/FeedController.php:68`).

Addressed by numeric `id` (owner-scoped via
`Auth::user()->feeds()->findOrFail($id)`), never by slug.

## Accepted Fields

From `UpdateFeedRequest` (`src/app/Http/Requests/Api/V1/UpdateFeedRequest.php`),
all `sometimes`:

| Field | Rule |
|---|---|
| `title` | sometimes, nullable, string, max 255 |
| `description` | sometimes, nullable, string, max 1000 |
| `website_url` | sometimes, nullable, string, url, max 255 |
| `is_public` | sometimes, boolean |
| `episode_order` | sometimes, nullable, enum `EpisodeOrderType` |

## `slug` Is Not Accepted (contract guarantee)

`slug` is **not** in the validated fields, so it is never mass-assigned. The API
has always behaved this way. After this feature, the **web** path is aligned to
the same guarantee.

**Implication for clients**: renaming a feed via the API updates `title` (and the
response reflects the new title), but the feed's `slug` — and therefore its
public RSS/share URL — is unchanged. The returned `slug` and `user_guid` are
stable across all updates.

## Response

`200` with `FeedResource` JSON, including the unchanged `slug` and `user_guid`.
