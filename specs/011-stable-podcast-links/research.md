# Phase 0 Research: Stable Podcast Links

**Feature**: 011-stable-podcast-links
**Date**: 2026-07-10

## Problem Statement

Public podcast links resolve via two URL segments: `{user_guid}/{slug}`. The
`user_guid` is a stable UUID set once at creation. The `slug`, however, is
derived from the feed **title** and is **regenerated on every save** in the web
`FeedController::update()` (`src/app/Http/Controllers/FeedController.php:86`),
even when the title is unchanged. Because both public controllers resolve feeds
with `where('user_guid', ...)->where('slug', ...)` (`ShareController.php:13-16`,
`RssController.php:15-17`), any slug change instantly 404s every previously
shared link — including podcast-app subscriptions, which cache the RSS URL and
treat it as permanent.

There is no redirect, alias, or slug-history mechanism today.

## Research Task 1: How should the public link behave across a rename?

### Decision

**Make the slug write-once (immutable after creation).** The slug is generated
from the title once at creation (`FeedController::store()`, line 36) and never
modified again. Renaming updates only the `title`; the URL is unchanged.

### Rationale

1. **Podcast-industry standard.** Apple, Google, and every major podcatcher
   cache the RSS feed URL a subscriber enters. Changing that URL severs the
   subscription — listeners stop receiving episodes and may never notice. Apple's
   own documentation treats the feed URL as a permanent commitment; the only
   sanctioned way to "move" a feed is `<itunes:new-feed-url>`, which still
   requires the old URL to keep serving. The strongest guarantee that links never
   break is to **never change the URL**.
2. **Matches the user's phrasing.** "Shouldn't break the links that already
   exist" asks for permanence, not graceful redirect. An immutable URL is
   permanence by construction; a redirect is permanence by recovery.
3. **It is the root-cause fix.** The slug is already a column independent of the
   title. The bug is purely that `update()` overwrites it. Removing that one
   assignment line fixes both the rename case (P1) and the unrelated-edit case
   (P2) at once.
4. **Strictly improves performance.** `generateUniqueSlug()` runs a DB probe loop
   on every save today. Removing it from `update()` eliminates those queries.

### Alternatives Considered

| Alternative | What it does | Why rejected |
|---|---|---|
| **Slug-history table + 301 redirect** (alias old slugs → current) | Keep regenerating slug on rename; store old slugs; redirect old URLs to new. | Far more moving parts (new table, redirect logic, collision handling against historic slugs). Redirects are unreliable in podcast-app HTTP clients (some ignore them, some treat a redirected feed as a *new* subscription). Breaks the guarantee for podcatchers that cache the original URL verbatim. Only justified if URLs were *required* to match the current title — they are not. |
| **Drop slug from URL entirely, resolve by `user_guid` alone** | Make `user_guid` the sole public key. | Works but changes every existing public URL (all currently-shared links break immediately) — the exact failure this feature exists to prevent. Also loses the human-readable segment. |
| **Status quo + documentation** | Tell users not to rename. | Does not satisfy the requirement; non-title edits already break links silently, so even careful users are affected. |
| **Allow opt-in slug edits (vanity URL)** | Let the owner change the slug deliberately. | Explicitly out of scope per the spec assumptions. Independent of this fix (could be layered on later without affecting immutability). |

## Research Task 2: Are episodes or media URLs affected by a rename?

### Decision

**No.** No episode-level change is needed.

### Rationale

- Episodes (`FeedItem` → `LibraryItem` → `MediaFile`) have **no public URL of
  their own**. They render inline within the RSS `<item>` list and the share
  page.
- RSS episode `<guid isPermaLink="false">` uses the `FeedItem` numeric id
  (`rss.blade.php:25`), not the slug. This is the dedup key podcatchers use, so a
  rename causes **no episode duplication**.
- Episode enclosure/media URLs are `/files/{file_path}` (+ `?feed_token=` for
  private feeds), built from `MediaFile::file_path`
  (`MediaFile.php:38-41`). Independent of the feed slug.

The entire breakage surface is the single `feeds.slug` column.

## Research Task 3: Is the web/API inconsistency a problem?

### Decision

**Yes — it must be unified, and the immutable-slug fix does so for free.**

### Rationale

- The **API** `FeedController::update()` (`Api/V1/FeedController.php:74`) already
  does `$feed->update($validated)`, and `UpdateFeedRequest` does **not** accept
  `slug` in its rules — so the API already leaves the slug untouched. (It lets
  title and slug drift apart, which is the desired stability but means the URL
  never matches a renamed title.)
- The **web** path overwrites the slug on every save — the bug.

Removing the slug assignment from the web `update()` makes both paths behave
identically: **slug is never changed after creation, via either interface**
(satisfying FR-008). No API change is required.

## Research Task 4: Is a data migration or backfill needed?

### Decision

**No.**

### Rationale

- The `slug` column already exists and already holds a value for every feed.
- Existing feeds have a slug that matches their current title (because it was
  last regenerated on their most recent save). After this change, that value
  simply stops changing going forward. Their current links already use the
  current slug and keep working.
- Links broken *before* this fix are already 404 and cannot be recovered without
  a slug-history mechanism (rejected above). This feature prevents **future**
  breakage, which is what the requirement asks for.
- No uniqueness risk: since the slug no longer changes on update, the existing
  `unique(['user_id', 'slug'])` constraint cannot be violated by a rename.
  Collisions are only possible at creation and are already handled by
  `generateUniqueSlug()`.

## Research Task 5: Should the now-dead `generateUniqueSlug($excludeFeedId)` parameter be removed?

### Decision

**Yes — from the web controller.** It becomes dead code as a direct result of
this change.

### Rationale

After removing the slug regeneration in `update()`, the web
`FeedController::generateUniqueSlug()` is only called from `store()` without the
`$excludeFeedId` argument. The parameter and its two body branches become dead.
Removing them keeps the code honest and matches the "deletion over addition"
principle.

> Note: the API `Api/V1/FeedController::generateUniqueSlug()` has the **same
> dead `$excludeFeedId` parameter even before this change** (its only caller is
> `store()` without it). That is pre-existing dead code, not introduced here.
> Cleaning it is an optional freebie; it is out of this feature's scope and left
> to the implementer's discretion.

## Open Questions

None. All decisions resolved with industry-standard defaults documented above.
