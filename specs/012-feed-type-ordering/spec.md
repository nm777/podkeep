# Feature Specification: Podcast Feed Types — Static Chapter Feeds vs Append-Style Feeds

**Feature Branch**: `012-feed-type-ordering`  
**Created**: 2026-07-12  
**Status**: Draft  
**Input**: User description: "Two different kinds of feeds: (a) one that is essentially chapters and won't be appended to and (b) one that is intended to be added to over time and should be sorted with the newest items at the top. Instead of simply choosing a sort order instead I want a way to choose the type of podcast, then if it is a static chapter-based feed, offer tools to manage sorting and ordering in a fixed way. The append-style version should allow people to add a date that can be used in the description, but it should always put the most recently added items at the top of the list so podcast apps display those as next-up."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Choose Feed Type When Creating a Feed (Priority: P1)

When a user creates a new feed, they choose between two feed types: **Static** (chapter-based, fixed content like an audiobook) or **Append** (ongoing content that grows over time like a traditional podcast). The feed type determines how episodes are ordered and what management tools are available. This replaces the current "Episode Order" dropdown (Newest First / Chronological).

**Why this priority**: The feed type is the foundational choice that governs all subsequent behavior — ordering, pubDate strategy, and available tools. Without it, nothing else in this feature can function.

**Independent Test**: Create a new feed, select "Static" type, add episodes, and verify the feed defaults to manual ordering with sequence-based pubDates. Create a second feed, select "Append" type, add episodes at different times, and verify the most recently added episode appears first in the RSS feed.

**Acceptance Scenarios**:

1. **Given** a user is creating a new feed, **When** they see the feed type selector, **Then** they can choose between "Static (Chapters)" and "Append (Ongoing)" with a clear description of each type
2. **Given** a user selects "Static" type, **When** they save the feed, **Then** the feed is configured for fixed chapter-style ordering with manual reordering tools
3. **Given** a user selects "Append" type, **When** they save the feed, **Then** the feed is configured to always show the most recently added items first
4. **Given** an existing feed with the old `episode_order` setting, **When** the feature is deployed, **Then** the feed is automatically migrated to a feed type without user intervention (see Assumptions for migration mapping)

---

### User Story 2 - Static Feed: Manual and Bulk Chapter Ordering (Priority: P2)

A user with a Static feed can reorder episodes using both fine-grained drag-and-drop and bulk quick-sort actions. Quick-sort options include: **Alphabetical** (A→Z by title), **Reverse Alphabetical** (Z→A), **Chronological** (oldest first by publish/addition date), and **Reverse Chronological** (newest first). These bulk actions are especially useful after an initial import where many files need reordering — dragging each one individually would be tedious. After a quick-sort, the user can still fine-tune with drag-and-drop. The RSS feed generates pubDates derived from each episode's sequence position (not the item's creation timestamp), ensuring podcast apps like Apple Podcasts display episodes in the user's intended order regardless of the app's sort setting. Once arranged, the order stays fixed until the user explicitly changes it.

**Why this priority**: This directly solves the ordering problem the user reported — episodes appearing out of order in Apple Podcasts because pubDates didn't match the intended sequence. The bulk sort options also solve the tedious manual reordering problem when an initial import produces a wrong order.

**Independent Test**: Create a Static feed with 21 episodes imported in reverse order. Click "Chronological" quick-sort to instantly correct the order. Drag one episode to fine-tune. Save and verify the RSS feed's pubDates match the new sequence.

**Acceptance Scenarios**:

1. **Given** a Static feed with episodes in a specific order, **When** the user views the edit page, **Then** episodes appear in the order determined by their sequence, with the newest pubDate at the top
2. **Given** a Static feed, **When** the user clicks a quick-sort option (e.g., "Alphabetical"), **Then** all episodes instantly rearrange to match that sort order without requiring individual dragging
3. **Given** a Static feed where the user applied a quick-sort, **When** the user drags an individual episode to fine-tune, **Then** only that episode moves and the rest retain their positions
4. **Given** a Static feed, **When** the user drags an episode to a new position and saves, **Then** the RSS feed regenerates with pubDates matching the new sequence order
5. **Given** a Static feed, **When** a podcast app reads the RSS feed, **Then** episodes appear in the user's arranged order because pubDates are sequence-derived (1 minute apart, earliest sequence = earliest pubDate)
6. **Given** a Static feed, **When** a new item is added to the feed, **Then** it is appended to the end of the sequence and gets the next available pubDate

---

### User Story 3 - Append Feed: Newest Items at Top with Optional Date (Priority: P3)

A user with an Append feed adds episodes over time. Each new episode automatically appears at the top of the feed (most recently added = newest pubDate). The user can optionally attach a display date to each episode that appears in the RSS description or title, providing context for listeners (e.g., "Recorded on July 4, 2026"). Ordering is always by addition recency — no manual reordering needed.

**Why this priority**: This serves the ongoing-podcast use case where new content should surface immediately. The optional display date adds metadata richness without affecting sort order.

**Independent Test**: Create an Append feed, add 3 episodes on different days, verify the most recently added appears first in the RSS. Add a display date to one episode and verify it appears in the description.

**Acceptance Scenarios**:

1. **Given** an Append feed, **When** a user adds a new episode, **Then** it appears at the top of the RSS feed (most recent pubDate) so podcast apps show it as the next episode
2. **Given** an Append feed, **When** the user adds an optional display date to an episode, **Then** that date is included in the episode's RSS description for listener context
3. **Given** an Append feed, **When** the user does NOT provide a display date, **Then** the episode still appears with a valid pubDate based on when it was added to the feed
4. **Given** an Append feed with multiple episodes, **When** viewed in a podcast app, **Then** episodes appear in reverse-chronological order by addition date (newest first)

---

### Edge Cases

- What happens when a user switches an existing feed from Static to Append (or vice versa)? Existing sequence ordering is preserved for Static; for Append, items are re-ordered by addition date.
- What happens when a Static feed has episodes added after the initial arrangement? New episodes append to the end (oldest pubDate), not the top.
- What happens if a user removes an episode from the middle of a Static feed? Remaining episodes' pubDates are recalculated to maintain consistent spacing.
- How does the system handle a feed with zero episodes? The RSS feed is still valid but contains no `<item>` elements.
- What happens if a display date on an Append feed episode is set to the future? It's allowed (used for description context only, not for ordering).
- What happens when a user applies a quick-sort after manual drag-and-drop reordering? The quick-sort overwrites the manual arrangement entirely. The user can then fine-tune with drag-and-drop again.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a feed type selector with two options when creating or editing a feed: "Static (Chapters)" and "Append (Ongoing)"
- **FR-002**: System MUST replace the existing Episode Order dropdown (Newest First / Chronological) with the feed type selector
- **FR-003**: For Static feeds, System MUST derive RSS pubDates from the episode's sequence position (deterministic spacing from a base date) so podcast apps display episodes in the user's arranged order
- **FR-004**: For Static feeds, System MUST provide both drag-and-drop reordering and bulk quick-sort actions (Alphabetical, Reverse Alphabetical, Chronological, Reverse Chronological) on the edit page that rearrange all episodes at once
- **FR-005**: For Append feeds, System MUST assign pubDates based on when each episode was added to the feed, with the most recently added episode receiving the newest pubDate
- **FR-006**: For Append feeds, System MUST always order episodes by addition recency (newest first) in the RSS feed
- **FR-007**: For Append feeds, System MUST provide an optional display date field per episode that, when set, appears in the RSS description
- **FR-008**: System MUST automatically migrate existing feeds to the new feed type on deployment without requiring user action (see Assumptions for mapping)
- **FR-009**: System MUST clear and regenerate the RSS cache when feed type or episode ordering changes
- **FR-010**: System MUST allow users to change a feed's type after creation, with appropriate reordering of episodes

### Key Entities *(include if feature involves data)*

- **Feed**: Gains a `feed_type` attribute (static | append) replacing `episode_order`. Determines ordering strategy and available management tools.
- **FeedItem / LibraryItem**: Append feeds use an optional `display_date` attribute on the library item for RSS description context. This date does not affect ordering — ordering is always by addition recency for Append feeds.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can select a feed type during feed creation in a single step without confusion (100% of test users understand the difference between Static and Append)
- **SC-002**: Episodes in a Static feed appear in the user's arranged order in Apple Podcasts within one feed refresh cycle, regardless of the app's sort setting
- **SC-003**: New episodes added to an Append feed appear at the top of the feed in podcast apps within one feed refresh cycle
- **SC-004**: Existing feeds continue to function correctly after automatic migration with zero data loss or ordering regressions
- **SC-005**: The optional display date on Append feed episodes appears correctly in the RSS description for 100% of episodes where it is set

## Assumptions

- **Migration mapping**: Existing feeds with `episode_order: chronological` migrate to `feed_type: static`. Existing feeds with `episode_order: newest_first` migrate to `feed_type: append` (since newest-first implies ongoing content where new items surface at the top). Users can change the type post-migration.
- **Static pubDate base**: The base date for sequence-derived pubDates uses the feed's creation date, with each episode spaced 1 minute apart by sequence. This provides deterministic, unambiguous ordering for podcast apps.
- **Append pubDate**: Uses the feed item's creation timestamp (when the item was added to the feed). If multiple items are added at nearly the same time, sequence provides tiebreaking.
- **Display date scope**: The optional display date on Append feeds is metadata for listener context (shown in RSS description), not a sorting field. Sorting is always by addition recency.
- **Feed type is not permanent**: Users can switch between Static and Append at any time. Switching re-sorts episodes appropriately (Static → by sequence; Append → by addition date).
- **Apple Podcasts caching**: PodKeep cannot control how quickly Apple Podcasts refreshes its cache. The pubDate strategy ensures correct ordering once the feed is refreshed, but refresh timing is Apple's behavior.
