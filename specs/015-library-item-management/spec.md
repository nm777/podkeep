# Feature Specification: Improved Library Item Management

**Feature Branch**: `015-library-item-management`  
**Created**: 2026-07-24  
**Status**: Draft  
**Input**: User description: "adding library items needs to be improved. I want the ability to hide some feeds from the list of feeds to select from when adding a new media item. Also, when editing a feed the list of media items is too small to be useful and cannot be searched. On the feed edit page, I want a way to tab back and forth between the list of items on the feed and the list of other media I can add. The media list should also be searchable like the feed list is today."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Tabbed, Searchable Media Picker on the Feed Editor (Priority: P1)

A user is editing a feed and wants to add existing library items to it. Today the list of addable media is a tiny, fixed-height box buried beneath the current feed items, and it cannot be searched, so finding the right media means visually scanning a cramped list. The user wants the feed editor to present two clear views they can switch between instantly: "what's already on this feed" and "what else from my library I can add." The "add media" view should be roomy and searchable by title, exactly like the feeds list search they already use, so they can type a few characters and find the item to add in seconds.

**Why this priority**: This is the direct "adding library items needs to be improved" pain point. A tiny, unsearchable list is the most common frustration when building out a feed, and the tabbed layout removes the scroll-to-find workflow entirely. It delivers standalone value on its own.

**Independent Test**: Can be fully tested by opening any feed's edit screen, switching to the "Add Media" view, typing a search term, and confirming the matching library items appear in a tall, usable list that can be added with one click — without touching the feed-selection feature at all.

**Acceptance Scenarios**:

1. **Given** a feed with some items already added and a library of many more items, **When** the user opens the feed editor, **Then** they see two distinct, switchable views (e.g., "Feed Items" and "Add Media") and can move between them in a single action without scrolling past one to reach the other.
2. **Given** the "Add Media" view is open, **When** the user types part of an item's title into the search box, **Then** the list filters in real time to matching library items, mirroring the existing feeds-list search behavior.
3. **Given** the "Add Media" view is open, **When** the list is displayed, **Then** it shows enough items at once to be usable (substantially taller than the previous fixed-height box) and is scrollable for large libraries.
4. **Given** a library item is already on the current feed, **When** the user views the "Add Media" list, **Then** that item does not appear as addable.
5. **Given** the user adds an item from the "Add Media" view, **When** they switch back to the "Feed Items" view, **Then** the newly added item appears in the feed's item list.

---

### User Story 2 - Hide Feeds from the Add-Media Selector (Priority: P2)

A user maintains some feeds they never manually add media to (for example, an auto-managed or archival feed). When adding a new media item, the "Add to Feeds" selector lists every feed they own, cluttering the picker with feeds they never select. The user wants a per-feed setting to hide chosen feeds from that selection list, so the picker only shows the feeds they actively add media to. Hidden feeds continue to work normally everywhere else.

**Why this priority**: Valuable for organizing the picker but not blocking; the picker is usable today (just cluttered). It is fully independent of Story 1 and can ship and be tested on its own.

**Independent Test**: Can be fully tested by marking a feed as hidden, opening the "Add Media" dialog, and confirming the hidden feed no longer appears among the selectable feeds — while confirming the feed is still visible on the dashboard and its RSS output is unchanged.

**Acceptance Scenarios**:

1. **Given** the user owns several feeds, **When** they open a feed's settings, **Then** they can toggle whether that feed appears in the add-media feed-selection list.
2. **Given** a feed has been toggled off (hidden), **When** the user adds a new media item and opens the "Add to Feeds" selector, **Then** the hidden feed is not listed.
3. **Given** a feed has been toggled off, **When** the user views their dashboard, **Then** the feed is still listed and fully usable (view, edit, share, RSS).
4. **Given** the user re-enables a previously hidden feed, **When** they next open the add-media selector, **Then** the feed reappears in the list.
5. **Given** any existing or newly created feed, **When** it is first created or first viewed after this feature ships, **Then** it defaults to "shown" in the add-media selector (no existing feeds are hidden by default).

---

### Edge Cases

- A user hides **all** of their feeds: the add-media selector shows no feeds. This is acceptable — adding media without selecting a feed is already valid (the item simply lives in the library). The empty state should communicate that no selectable feeds are available and how to un-hide one.
- A user has a very large library: the "Add Media" search must still return matches quickly even with hundreds of items. (See Assumptions regarding the current cap; the full personal library should be searchable.)
- A library item is still processing (no playable media yet): it should still appear in the "Add Media" list, clearly labeled as processing (matching current behavior), so it can be queued into a feed.
- A feed is hidden while an add-media dialog is already open: the change takes effect the next time the selector is opened; the currently open dialog need not update live.
- Hiding a feed must never remove items already attached to it, affect its RSS output, or change existing memberships.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a per-feed setting that controls whether the feed appears in the feed-selection list shown when adding new media.
- **FR-002**: When adding new media, the feed-selection list MUST include only feeds the user has not hidden via this setting.
- **FR-003**: The feed-selection visibility setting MUST default to "shown" for every new and existing feed; no feed is hidden automatically by the introduction of this feature.
- **FR-004**: The feed editor MUST provide two distinct, switchable views of items: current feed items, and library media available to be added — navigable between in a single action (tabs).
- **FR-005**: The "available media to add" view MUST support searching items by title, with behavior consistent with the existing feeds-list search.
- **FR-006**: The "available media to add" view MUST be large enough to be usable (substantially taller than the previous fixed-height box) and must surface the user's full eligible library, not a hard-capped subset.
- **FR-007**: Library items already attached to the current feed MUST NOT appear in the "available media to add" view.
- **FR-008**: Hiding a feed from the add-media selector MUST have no effect on its dashboard visibility, editability, shareability, RSS output, or any existing feed memberships.
- **FR-009**: The feed-selection visibility setting MUST be changeable from the feed's own settings/edit screen, alongside the feed's other settings.

### Key Entities *(include if feature involves data)*

- **Feed**: Gains a new per-feed attribute indicating whether it should be offered in the feed-selection list when adding new media (default: offered). All existing Feed behavior (ownership, items, RSS, sharing) is unchanged by this attribute.
- **LibraryItem**: Unchanged structurally; it is the media listed and searched in the feed editor's "available media to add" view.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A user can locate and add a specific library item to a feed from the feed editor by typing a few characters of its title, completing the add within 10 seconds regardless of library size.
- **SC-002**: A user can switch between viewing a feed's current items and its available-to-add media in a single action (one click), without scrolling.
- **SC-003**: The "available media to add" list accommodates the user's entire personal media library (all eligible items) within a searchable view, rather than a fixed, capped window.
- **SC-004**: A user can reduce their add-media feed-selection list to only the feeds they actively add media to, by hiding the rest.
- **SC-005**: Hiding a feed from the selector has zero effect on its published RSS feed, its dashboard presence, or existing feed memberships (qualitative correctness verified by testing).

## Assumptions

- "Hide feeds" applies **only** to the add-media feed-selection list. The feed remains visible and fully functional everywhere else (dashboard, sharing, RSS).
- The feed-selection visibility setting is managed on the feed's own edit screen alongside its other settings, defaulting to "shown."
- The media search on the feed editor is the same client-side, title-based filtering already used by the feeds list, keeping behavior and performance consistent.
- The current cap that passes only a limited subset of library items to the feed editor will be raised/removed so the full personal library is searchable client-side. Personal libraries are modest; if they grow very large, server-side/infinite-scroll search can be added later.
- There is no "show hidden feeds" toggle inside the add-media picker for now (out of scope); hidden feeds are managed solely from their feed settings.
