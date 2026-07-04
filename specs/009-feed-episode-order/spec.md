# Feature Specification: Per-Feed Episode Ordering

**Feature Branch**: `009-feed-episode-order`
**Created**: 2026-07-04
**Status**: Draft
**Input**: User description: "Some feeds are books with chapters. In this case, the list of 'episodes' shows in the reverse order since the oldest timestamp is the first chapter. I'd like a way to make a feed simple to play from beginning to end in order."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Episode Order Toggle and Manual Reordering (Priority: P1)

A user has an audiobook feed where each "episode" is a chapter. Currently, episodes appear newest-first (standard podcast ordering), which means chapter 1 appears at the bottom of the list and the final chapter appears at the top — the reverse of what the user wants. The user needs two things: (1) a per-feed setting to switch the default episode display order to "chronological" (oldest first), and (2) the ability to manually drag-and-drop individual episodes to fix any that are out of sequence — for example, when chapters were uploaded in the wrong order. Together, these let the user arrange a feed so it plays correctly from beginning to end.

The order setting can be changed at any time on existing feeds that already have uploaded media — no need to decide at creation time. When the user switches a feed to chronological, the existing episodes are simply displayed in ascending sequence order. If any episode is in the wrong position, the user opens the feed edit page, drags it to the correct spot, and saves. The corrected order is reflected everywhere: RSS feed, share player, and podcast clients.

**Why this priority**: This is the complete core feature. The order toggle solves the default direction problem, and manual reordering handles the inevitable case where uploads arrive out of order. Both are needed for the feature to be useful for audiobooks.

**Independent Test**: Can be fully tested by creating a feed, uploading episodes in scrambled order, switching the feed to chronological, opening the edit page, drag-and-dropping episodes into the correct chapter order, and verifying the corrected order appears in the RSS feed and share player.

**Acceptance Scenarios**:

1. **Given** a user has a feed with 5 episodes that were uploaded out of order, **When** the user sets the feed's episode order to "chronological" and opens the feed edit page, **Then** episodes are listed by sequence (allowing the user to see the current order and identify what needs fixing).
2. **Given** a chronological feed where chapter 3 appears before chapter 2, **When** the user drags chapter 2 above chapter 3 on the edit page and saves, **Then** the sequence values are updated and chapter 2 now appears before chapter 3 in the RSS feed and share player.
3. **Given** a user has set a feed to "chronological" order, **When** someone opens the share player for that feed, **Then** episodes are listed oldest-first (chapter 1 at the top) in the manually arranged order.
4. **Given** a user changes a feed from "newest first" to "chronological" (or vice versa) on a feed that already has 20 uploaded episodes, **Then** no episodes are lost or re-sequenced — the same sequence values are simply read in the opposite direction.
5. **Given** a new feed is created, **When** no episode order is explicitly set, **Then** the feed defaults to "newest first" (standard podcast behavior), preserving existing behavior for all current users.

---

### User Story 2 - Auto-Append for Chronological Feeds (Priority: P2)

When a user with a "chronological" feed uploads a new chapter or adds a new episode, the episode should be appended to the end of the feed (receiving the next available sequence number) rather than being inserted at the beginning. This reduces the need for manual reordering after every upload — the new chapter lands in the right spot automatically. The user can still drag-and-drop to adjust if needed.

**Why this priority**: This improves the day-to-day workflow for chronological feeds but is not required for the core ordering to work — users can manually reorder via drag-and-drop. It prevents frustration when adding new chapters.

**Independent Test**: Can be tested by creating a chronological feed with existing episodes, uploading a new episode, and verifying it appears at the end (highest sequence) rather than the beginning.

**Acceptance Scenarios**:

1. **Given** a chronological feed has 3 episodes with sequences 0, 1, 2, **When** the user uploads a new episode and attaches it to the feed, **Then** the new episode receives sequence 3 and appears last in the feed.
2. **Given** a "newest first" feed has 3 episodes, **When** the user uploads a new episode and attaches it to the feed, **Then** the new episode receives sequence 0 (or the lowest sequence) and appears first in the feed, preserving existing podcast behavior.

---

### Edge Cases

- What happens when a user switches a feed from "newest first" to "chronological" after episodes already exist? The existing sequence values are respected — the feed simply displays them ascending instead of descending. No re-sequencing is needed.
- What happens when a user switches from "chronological" to "newest first"? Episodes display in reverse sequence order. No data migration needed.
- What happens when two episodes share the same sequence value? The system must handle this gracefully (e.g., tiebreak by insertion order or library item timestamp) so the display order is deterministic.
- What happens when a feed has no episodes? The order setting has no visible effect but is stored for when episodes are added.
- What happens if a user reorders episodes, then changes the order setting? The manually arranged sequence values are preserved — only the display direction changes.
- How do podcast clients handle this? Podcast clients (Apple Podcasts, Pocket Casts, etc.) typically sort by `<pubDate>` internally, overriding feed document order. For chronological feeds, the RSS `<pubDate>` values should ascend with episode sequence so that podcast clients naturally display oldest-first. The in-app share player respects sequence ordering directly.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a per-feed episode order setting with at least two options: "newest first" (default) and "chronological" (oldest first).
- **FR-002**: System MUST default new feeds to "newest first" to preserve existing behavior for all current feeds.
- **FR-003**: System MUST allow users to change the episode order setting at any time on any feed, including feeds that already have uploaded episodes.
- **FR-004**: System MUST allow users to manually reorder individual episodes within a feed via drag-and-drop on the feed edit page, regardless of the feed's order setting.
- **FR-005**: System MUST load episodes on the feed edit page ordered by sequence, so the user sees the current playback order on initial page load (not insertion order).
- **FR-006**: System MUST persist manually reordered sequence values when the user saves, so the corrected order is reflected across all surfaces.
- **FR-007**: System MUST apply the selected episode order to the RSS feed output, ordering episodes by sequence in the chosen direction.
- **FR-008**: System MUST apply the selected episode order to the share player, which already sorts by sequence — ensuring chronological feeds display oldest-first.
- **FR-009**: System MUST order RSS `<pubDate>` values consistently with the episode order setting, so that podcast clients that sort by pubDate display episodes in the user's chosen order.
- **FR-010**: When a new episode is added to a "chronological" feed, System MUST assign it the next available sequence number (appending to the end).
- **FR-011**: When a new episode is added to a "newest first" feed, System MUST assign it a sequence that places it at the top, preserving existing behavior.
- **FR-012**: System MUST NOT re-sequence existing episodes when the order setting is changed — the same sequence values are simply read in a different direction.
- **FR-013**: System MUST expose the episode order setting through the REST API (both read and write).

### Key Entities *(include if feature involves data)*

- **Feed (existing)**: Gains a new "episode order" attribute (values: "newest first" or "chronological"). This controls how the feed's episodes are sorted in all display surfaces. All existing attributes and relationships remain unchanged.
- **FeedItem (existing pivot)**: The `sequence` column already exists and is used for ordering. No schema changes needed for the ordering feature — the sequence values are manually adjustable and interpreted based on the feed's order setting.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can change a feed's episode order between "newest first" and "chronological" in under 10 seconds from the feed edit page.
- **SC-002**: Users can drag-and-drop a misplaced episode to its correct position in under 5 seconds on the feed edit page.
- **SC-003**: Audiobook feeds set to chronological order display chapter 1 first and the final chapter last across all surfaces (RSS, share player, edit view).
- **SC-004**: 100% of existing feeds retain their current display behavior (newest first) with no action required by users.
- **SC-005**: Manually reordered episode positions persist correctly across RSS feed regeneration, share player display, and page reloads.
- **SC-006**: Podcast clients consuming a chronological RSS feed display episodes oldest-first (verified via pubDate ascending order).
