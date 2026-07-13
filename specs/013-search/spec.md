# Feature Specification: Search Across Library, Feeds, and Feed Items

**Feature Branch**: `013-search`  
**Created**: 2026-07-12  
**Status**: Draft  
**Input**: User description: "add search to the Library and also within a feed and on the feed list."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Search the Library (Priority: P1)

A user types a search query on the Library tab of the dashboard. The library item list filters in real time to show only items whose title matches the query. Clearing the search restores the full list. This covers the most common use case: finding a specific media item among many.

**Why this priority**: The library is the primary content store. Users with dozens or hundreds of items need search to find specific episodes, chapters, or audio files quickly.

**Independent Test**: Add 10 library items with distinct titles. Type a search query matching one title. Verify only that item appears. Clear the search. Verify all 10 items reappear.

**Acceptance Scenarios**:

1. **Given** the user is on the Library tab with multiple items, **When** they type a search term, **Then** only items whose titles contain the term are shown
2. **Given** a search is active showing filtered results, **When** the user clears the search box, **Then** all items are shown again
3. **Given** the user types a search term that matches no items, **Then** a "No results found" message is displayed
4. **Given** a search is active, **When** the user switches to the Feeds tab and back, **Then** the search is reset to show all items

---

### User Story 2 - Search Within a Feed's Item List (Priority: P2)

On the feed edit page, the user can search through the feed's items to quickly locate a specific episode among many (e.g., 21 audiobook chapters). The item list filters as the user types, showing only items whose titles match. The drag-and-drop ordering of hidden items is preserved when the search is cleared.

**Why this priority**: Feeds with many episodes (like audiobooks with 20+ chapters) are tedious to scroll through. Search lets the user find and reorder specific items quickly.

**Independent Test**: Open a feed with 10 items. Type a search query matching one item. Verify only that item shows. Clear the search. Verify all 10 items reappear in their original order.

**Acceptance Scenarios**:

1. **Given** the user is editing a feed with many items, **When** they type a search term in the item search box, **Then** only matching items are displayed
2. **Given** a search filter is active on the items list, **When** the user clears the search, **Then** all items reappear in their original sequence order
3. **Given** the user is searching within a feed's items, **When** they reorder a visible item via drag-and-drop and clear the search, **Then** the reordering is preserved

---

### User Story 3 - Search the Feed List (Priority: P3)

On the Feeds tab of the dashboard, the user can search their feeds by title. As they type, the feed list filters to show only feeds whose titles match the query. This helps users who have many feeds find the one they want to edit or share.

**Why this priority**: Users with many feeds need to locate specific ones quickly. This mirrors the library search but for the feed list.

**Independent Test**: Create 5 feeds with distinct titles. Type a search query matching one feed title. Verify only that feed appears. Clear the search. Verify all 5 feeds reappear.

**Acceptance Scenarios**:

1. **Given** the user is on the Feeds tab with multiple feeds, **When** they type a search term, **Then** only feeds whose titles contain the term are shown
2. **Given** a search is active, **When** the user clears the search box, **Then** all feeds are shown again
3. **Given** a search returns no results, **Then** a "No feeds found" message is displayed

---

### Edge Cases

- What happens when the user types a very long search query? The filter still applies (no truncation needed — it just won't match anything).
- What happens with special characters in the search? The search is case-insensitive substring matching; special characters are treated literally.
- Does search persist across tab switches? No — switching tabs resets the search to show all items.
- Does search persist across page navigations? No — navigating away and back resets the search.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a search input on the Library tab that filters library items by title in real time as the user types
- **FR-002**: System MUST provide a search input on the feed edit page that filters the feed's items by title in real time
- **FR-003**: System MUST provide a search input on the Feeds tab that filters feeds by title in real time
- **FR-004**: Search MUST be case-insensitive substring matching (typing "lord" matches "Lord of the Rings")
- **FR-005**: Clearing the search input (empty string) MUST restore the full unfiltered list
- **FR-006**: Search MUST display a "no results" message when no items match the query
- **FR-007**: Search on the feed edit page MUST NOT affect the underlying sequence order — hidden items retain their positions when the filter is cleared
- **FR-008**: Search MUST reset when switching tabs on the dashboard (Library ↔ Feeds)

### Key Entities

No new entities. Search operates on existing data:
- **LibraryItem**: searched by `title` field
- **Feed**: searched by `title` field
- **FeedItem** (within a feed): searched by the associated LibraryItem's `title` field

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can find a specific library item by typing a partial title and seeing results appear within 200ms
- **SC-002**: Users can find a specific feed by typing a partial title and seeing results appear within 200ms
- **SC-003**: Users can locate a specific episode within a 20+ item feed by typing a partial title
- **SC-004**: Clearing search instantly restores the full list with no visible delay
- **SC-005**: Feed item ordering is preserved after searching and clearing on the edit page (zero reordering regressions)

## Assumptions

- **Client-side filtering**: Search filters the already-loaded data client-side (no server round-trips). The dashboard already loads all feeds and library items; the feed edit page already loads all feed items. No pagination is in play for these lists.
- **Search scope**: Title field only. Description and other metadata are not searched. This can be expanded later if needed.
- **Debounce**: A short debounce (150–200ms) on the search input prevents excessive re-filtering while typing fast.
- **No persistence**: Search queries are not saved between page loads or tab switches. Each visit starts with a clear search.
- **Accessibility**: The search input is a standard text field with a label and placeholder, keyboard-accessible.
