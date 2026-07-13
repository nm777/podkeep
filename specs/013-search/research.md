# Research: Search

**Feature**: 013-search  
**Date**: 2026-07-12

## R1: Client-Side vs Server-Side Search

**Decision**: Client-side filtering only.

**Rationale**: The dashboard already loads all feeds and library items (via Inertia props). The feed edit page already loads all feed items. No pagination exists on these lists. Client-side filtering on already-loaded data is instant (< 1ms for hundreds of items) and requires zero backend changes.

**Alternatives considered**:
- Server-side search endpoint → rejected (unnecessary for small datasets already in memory; adds latency and complexity)
- Meilisearch/Algolia → rejected (massive overkill for personal podcast libraries with tens to low hundreds of items)

## R2: Debounce Strategy

**Decision**: 150ms debounce on the search input value.

**Rationale**: Without debounce, every keystroke triggers a re-filter of the list. For lists with 50-200 items, this is imperceptible, but debounce prevents unnecessary re-renders during fast typing. 150ms is short enough to feel instant but avoids re-filtering on every intermediate keystroke.

**Implementation**: A `useDebouncedValue` hook wraps the input value. The filtering logic reads the debounced value, not the raw input. The input updates immediately (no lag); only the filtering is debounced.

**Alternatives considered**:
- No debounce → works but causes unnecessary re-renders during fast typing
- 300ms debounce → feels slightly laggy for short lists

## R3: Search Component Reuse

**Decision**: Single reusable `SearchInput` component used in all three locations (Library tab, Feeds tab, feed edit page).

**Rationale**: The search UI is identical in all three contexts — a text input with a search icon and clear button. A shared component ensures visual consistency and reduces code duplication. The component takes `value`, `onChange`, and `placeholder` props.

**Alternatives considered**:
- Inline input in each page → rejected (code duplication, potential inconsistency)
- Three separate components → rejected (unnecessary; behavior is identical)

## R4: Feed Edit Page — Filtering vs Hiding

**Decision**: The search filters which items are VISIBLE in the list, but does not remove items from the underlying form data array. Hidden items retain their positions and sequences.

**Rationale**: The feed edit page uses `data.items` (from `useForm`) for both display and submission. If search removed items from the array, they'd be deleted on save (via `syncFeedItems`). Instead, the search creates a filtered view array (`data.items.filter(...)`) for rendering, while `data.items` stays intact. Drag-and-drop works on the filtered view; sequence reassignment accounts for visible items only.

**Alternatives considered**:
- Remove hidden items from form data → rejected (would delete items on save)
- Disable drag-and-drop during search → acceptable fallback but not needed if filtering is view-only

## R5: Tab Switch Behavior

**Decision**: Search resets when switching between Library and Feeds tabs on the dashboard.

**Rationale**: Each tab has its own search context. A library search query shouldn't persist when switching to feeds. The search state is local to each tab's rendering section, not shared. When the tab changes, the component for the previous tab unmounts (or its search state resets), starting fresh.
