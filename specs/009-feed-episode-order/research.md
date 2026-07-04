# Phase 0: Research — Per-Feed Episode Ordering

**Feature**: 009-feed-episode-order
**Date**: 2026-07-04

## Research Tasks

### R1: Episode Order Model — Enum Values and Semantics

**Decision**: Add an `EpisodeOrderType` backed string enum with two values:
- `NewestFirst` (`'newest_first'`) — default — items ordered by sequence **descending** (highest sequence first)
- `Chronological` (`'chronological'`) — items ordered by sequence **ascending** (lowest sequence first)

The existing `sequence` column on `feed_items` stores the manual ordering from drag-and-drop. The `episode_order` field simply controls the **direction** that sequence is read. No re-sequencing is needed when switching modes.

**Rationale**:
- The `sequence` column already exists and drag-and-drop already reorders it correctly. The user's problem is purely about display direction — for podcasts, newest at top (DESC); for audiobooks, chapter 1 at top (ASC).
- A two-value enum is the simplest possible model. No `published_at`-based ordering needed — sequence IS the ordering, and manual reordering lets users fix any position.
- This is consistent with how the share player already works (`sortBy('sequence')` ascending) — we just need to add the ability to reverse it.
- The enum follows the existing `ProcessingStatusType` pattern (backed string, TitleCase keys, lowercase values, `getDisplayName()` and boolean helper methods).

**Alternatives considered**:
- **Three-value enum** (manual, published_desc, published_asc): Overly complex. The user didn't ask for timestamp-based ordering — they want manual control with a direction toggle. Adding `published_at`-based modes would introduce null-handling complexity (many items lack `published_at`) and create confusion about which ordering wins.
- **Boolean `is_chronological` flag**: Less expressive than an enum and harder to extend if more order modes are added later. Enum is the project convention.
- **Per-item ordering override**: Would allow different episodes within the same feed to use different ordering. Overly granular and confusing.

---

### R2: RSS pubDate Handling for Podcast Clients

**Decision**: Output `<item>` elements in the RSS XML ordered by sequence in the feed's chosen direction. Do **not** modify or synthesize `<pubDate>` values — use the existing `libraryItem.published_at ?? feedItem.created_at` as-is.

**Rationale**:
- The RSS 2.0 spec does not require items to be in any particular order — document order is advisory. Most podcast clients sort by `<pubDate>` internally regardless of document order.
- Synthesizing fake pubDate values (e.g., based on sequence position) would be dishonest metadata and could confuse clients that show "release date" to users.
- For chronological feeds where chapters were uploaded in order, the `published_at` or `created_at` values already ascend naturally — podcast clients will show them oldest-first without any special handling.
- If a user uploaded chapters out of order, the in-app share player respects sequence ordering directly (no pubDate involved). For podcast clients, the user can set `published_at` values to match their desired chapter order — this is a reasonable manual step for the audiobook use case.
- The document ordering fix (ordering by sequence instead of insertion order) is still valuable as a tiebreaker and for clients that respect document order.

**Alternatives considered**:
- **Synthesize pubDate from sequence position** (e.g., incrementing by 1 second per chapter): Creates fake metadata. Risks confusing podcast clients that deduplicate by pubDate. Dishonest to the user.
- **Force pubDate to match sequence order**: Would require overriding real published_at values in the RSS output. Misleading and could break features that rely on real pubDate.

**Implication**: Document this as a known limitation in the quickstart guide — for podcast client compatibility with chronological audiobook feeds, users should set `published_at` on their library items to match the desired chapter order.

---

### R3: Auto-Append Behavior for New Episodes

**Decision**: The existing `AddLibraryItemToFeedsJob` logic (`max(sequence) + 1`) is already correct for both episode order modes. No code changes needed for auto-append.

**Rationale**:
- Current behavior: new items get `max(sequence) + 1` (the highest sequence value).
- In `newest_first` mode (sequence DESC): highest sequence appears at the **top** — correct for podcasts (newest episode at top).
- In `chronological` mode (sequence ASC): highest sequence appears at the **bottom** — correct for audiobooks (new chapter appended to end).
- Both modes work correctly with the same `max(sequence) + 1` logic because the display direction is what differs, not the sequence assignment.

**Alternatives considered**:
- **Mode-aware sequence assignment** (e.g., `min(sequence) - 1` for newest_first): Would be needed if the display direction were the same for both modes. But since the display direction differs, the same max+1 produces the correct position in both cases.
- **Re-sequencing on mode switch**: Not needed — switching modes only changes the read direction, not the stored values. Sequence values are preserved.

---

### R4: Feed::items() Relationship Default Ordering

**Decision**: Add `->orderBy('sequence')` to the `Feed::items()` relationship as a default ascending order. Each rendering surface then applies direction based on `episode_order`.

**Rationale**:
- Currently the relationship is a bare `hasMany` with no ordering, causing items to come out in DB insertion order. This is a latent bug — the RSS feed ignores the user's manual sequence entirely.
- Adding `orderBy('sequence')` to the relationship makes ALL callers automatically respect sequence ordering, including `RssController`, `FeedController::edit`, and any future code.
- The direction (ASC vs DESC) is applied at the rendering surface since different surfaces may need different directions.
- The `FeedController::edit` page should load items in ASCENDING order (sequence 0 first) so the user sees the chronological arrangement. The drag-and-drop hook works regardless of initial order (it re-indexes on drop).

**Alternatives considered**:
- **Apply ordering only in each controller**: More explicit but risks forgetting a surface. The relationship default is safer.
- **Apply direction in the relationship**: Would require the relationship to know about `episode_order`, which creates a coupling between the model and the rendering context. Better to keep the relationship ordering direction-agnostic (always ASC) and let each surface decide DESC when needed.

---

## Summary of Decisions

| # | Question | Decision |
|---|----------|----------|
| R1 | Order model | Two-value enum: `newest_first` (sequence DESC), `chronological` (sequence ASC) |
| R2 | RSS pubDate | Use real published_at/created_at; order XML by sequence; document podcast client limitation |
| R3 | Auto-append | Existing `max(sequence) + 1` already correct for both modes — no changes needed |
| R4 | Relationship ordering | Add `orderBy('sequence')` default to `Feed::items()` relationship |
