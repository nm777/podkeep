# Contract: Public Podcast URL (RSS + Share)

**Feature**: 011-stable-podcast-links

## Routes

Defined in `src/routes/web.php`:

| Method | Path | Name | Controller |
|---|---|---|---|
| GET | `/rss/{user_guid}/{feed_slug}` | `rss.show` | `RssController::show` |
| GET | `/share/{user_guid}/{feed_slug}` | `share.show` | `ShareController::show` |

## Permanence Guarantee (the contract this feature enforces)

The URL path `/rss/{user_guid}/{feed_slug}` (and `/share/...`) for a given feed
is **permanent for the lifetime of that feed**.

- `{user_guid}` is a UUID set once at creation; it never changes.
- `{feed_slug}` is derived from the title once at creation; after this feature it
  **never changes**, even when the feed is renamed or edited.

Therefore: any URL that ever resolved to a feed continues to resolve to that same
feed after any rename or edit, for as long as the feed exists. No redirects are
required because the URL never changes.

## Resolution

Both controllers resolve the feed by matching **both** segments:

```php
Feed::where('user_guid', $user_guid)->where('slug', $feed_slug)->...first();
```

- `ShareController::show` (`src/app/Http/Controllers/ShareController.php:13-16`)
- `RssController::show` (`src/app/Http/Controllers/RssController.php:15-17`)

A mismatch on either segment yields `404` (not `403` — private feeds' existence
is hidden).

## Access Control (unchanged)

- If `feed.is_public` is true: the URL is open.
- If `feed.is_public` is false: the request must carry `?token={feed.token}`.
  The token is a 64-char string set once at creation and is itself immutable.
  Mismatch yields `404`.

## Content After Rename (FR-004)

The URL stays the same, but the **content** reflects the new title:

- RSS feed: `<title>` and channel metadata use `$feed->title`, which updates on
  rename. The RSS output cache is keyed by `feed.id` and is cleared on update
  (`FeedController.php:96`), so the next request regenerates with the new title.
- Share page: rendered from `$feed->title` via Inertia props
  (`ShareController.php:53-57`).

## Episode GUIDs (FR-005 — no duplication)

RSS `<guid isPermaLink="false">` uses the `FeedItem` numeric id
(`rss.blade.php:25`), not the slug. Renaming the feed does not change any episode
GUID, so podcatchers will not treat existing episodes as new.
