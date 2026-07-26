# Contract: RSS Chapter Format

**Branch**: `016-chapter-markers` | **Date**: 2026-07-24

How authored chapters are published into the podcast feed so subscribing apps render them.

## Namespace

Add to the root `<rss>` element of `resources/views/rss.blade.php`:

```
xmlns:psc="http://podlove.org/simple-chapters"
```

(Podlove Simple Chapters — inline, self-contained, widely supported. See research.md R2.)

## Per-item rendering

Inside each feed `<item>` that has a media file **with chapters**, emit a single `<psc:chapters>` block containing one `<psc:chapter>` per chapter, ordered by `start_time` ascending. Only emit the block when the media file has ≥1 chapter (no empty `<psc:chapters>`).

```xml
<psc:chapters version="1.2">
    <psc:chapter start="0:00:00" title="Intro" />
    <psc:chapter start="0:05:30" title="Guest interview" />
    <psc:chapter start="1:12:45" title="Q&A" />
</psc:chapters>
```

- `start` is `start_time` (integer seconds) formatted as `H:MM:SS` (or `H:MM:SS` for ≥1h; zero-padded MM/SS, hours not padded). No fractional seconds (chapters are integer-second).
- `title` is XML-escaped (Blade `{{ }}` escapes by default).
- Chapters come from `$item->libraryItem->mediaFile->chapters` (already eager-loaded path: feed → items → libraryItem → mediaFile). The RSS query in `RssController::show` must add `items.libraryItem.mediaFile.chapters` to its eager-load list.

## Validity guarantee

`RssController` already re-parses the rendered view with `DOMDocument`, enables `libxml_use_internal_errors`, and **throws** if the XML is malformed (logging the errors). Inline `psc:chapters` is well-formed XML, so it cannot silently break the feed — any bad output fails loudly during cache population.

## Cache invalidation

RSS is cached under `rss.{feed_id}` (`Cache::remember` in `RssController::show`). The chapter sync path (`ChapterController@sync`) MUST clear that cache for **every feed containing any library item that uses the edited media file**:

```php
foreach ($mediaFile->libraryItems()->with('feedItems')->get() as $libItem) {
    foreach ($libItem->feedItems as $feedItem) {
        Cache::forget("rss.{$feedItem->feed_id}");
    }
}
```

This mirrors the existing invalidation in `LibraryController`. Without it, edited chapters stay stale until the RSS cache TTL.

## Behavior rules (testable)

1. A feed item whose media file has chapters MUST include the `<psc:chapters>` block.
2. A feed item whose media file has zero chapters MUST NOT include any chapter element.
3. Removing all chapters removes the block from the feed on next publish.
4. A media file shared by multiple feed items/feeds carries the same chapters into each.
5. The feed remains valid RSS 2.0 + namespaced XML at all times (enforced by `RssController` validation).
