# Research: Feed Type Ordering

**Feature**: 012-feed-type-ordering  
**Date**: 2026-07-12

## R1: Migration Strategy — `episode_order` → `feed_type`

**Decision**: Single migration renames the `episode_order` column to `feed_type` and maps values.

**Rationale**: The `episode_order` column (`varchar(20)`) already exists on the `feeds` table. Rather than adding a new column and dropping the old one (two schema changes), a single migration renames the column and updates the enum values. This is atomic and reversible.

**Value mapping**:
- `chronological` → `static` (user intended fixed ordering)
- `newest_first` → `append` (user intended ongoing content with newest at top)

**Alternatives considered**:
- Add new `feed_type` column, keep `episode_order` for backward compat → rejected (two sources of truth, confusing)
- Drop `episode_order`, add `feed_type` as separate step → rejected (extra migration, more risk)

## R2: pubDate Strategy per Feed Type

**Decision**:
- **Static feeds**: `feed.created_at + sequence minutes` (deterministic, 1-minute spacing)
- **Append feeds**: `feed_item.created_at` (actual addition timestamp)

**Rationale**: Apple Podcasts and most podcast apps sort by `<pubDate>`, ignoring XML item order. The pubDate must reflect the intended order:

- For Static feeds, the user's drag-and-drop/quick-sort arrangement determines sequence. Sequence-derived pubDates ensure podcast apps display episodes in the arranged order. The user sets Apple Podcasts to "Oldest to Newest" for chapter-style content.
- For Append feeds, the most recently added episode should appear as "next up." Using `feed_item.created_at` as the pubDate ensures new episodes naturally sort to the top.

**Alternatives considered**:
- Use `library_item.published_at` for both → rejected (not set for most items, leads to identical timestamps and ambiguous sorting)
- Use `feed_item.created_at` for both → rejected for Static (reordering wouldn't change pubDates, defeating the purpose)

## R3: Quick-Sort Implementation

**Decision**: Client-side array sort triggered by button clicks, persisted on save via the existing `syncFeedItems` mechanism.

**Rationale**: Quick-sort is a UI convenience that re-arranges the displayed items array. The sorted array is then saved using the existing form submission → `syncFeedItems` flow (which assigns sequence values from the frontend). No new backend endpoint needed — the sort happens in the React component state.

**Sort criteria**:
- Alphabetical: `libraryItem.title` ASC (case-insensitive)
- Reverse Alphabetical: `libraryItem.title` DESC
- Chronological: `libraryItem.published_at ?? feedItem.created_at` ASC
- Reverse Chronological: `libraryItem.published_at ?? feedItem.created_at` DESC

**Alternatives considered**:
- Backend sort endpoint → rejected (unnecessary complexity; the data is already client-side)
- Store sort preference per feed → rejected (quick-sort is a one-time bulk action, not a persistent preference)

## R4: Display Date for Append Feeds

**Decision**: Add nullable `display_date` column to `library_items` table. When set on an Append feed episode, it appears in the RSS `<description>` as a prefix: `[July 4, 2026] Original description`.

**Rationale**: The existing `published_at` field on LibraryItem was previously used as a pubDate fallback but is now unused for pubDate (replaced by sequence-based or creation-based dates). However, `published_at` has different semantic meaning (when the content was originally published). A separate `display_date` field is cleaner — it's purely for listener-facing context in the RSS description.

**Alternatives considered**:
- Reuse `published_at` → rejected (semantic confusion; published_at implies content publication date, not a display-only metadata field)
- Add `display_date` to `feed_items` → rejected (the date is about the content, not the feed membership)

## R5: Feed Type Switching

**Decision**: Users can switch feed type at any time. Switching triggers:
- **→ Static**: Existing sequences are preserved. If none exist, assigned by current display order.
- **→ Append**: Items are re-ordered by `feed_item.created_at` DESC (newest first). Sequences are reassigned to match addition order.

**Rationale**: The feed type determines the ordering STRATEGY, not just the direction. When switching to Append, the system takes over ordering (by addition recency). When switching to Static, the user's manual arrangement takes precedence.

**Alternatives considered**:
- Lock feed type after creation → rejected (user explicitly wants flexibility)
- Preserve sequences when switching to Append → rejected (Append ordering should be automatic, not based on stale manual sequences)
