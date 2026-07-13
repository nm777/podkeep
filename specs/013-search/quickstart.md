# Quick Start: Search

**Feature**: 013-search  
**Date**: 2026-07-12

## What This Feature Adds

Real-time client-side search filtering in three locations:
1. **Library tab** — filter library items by title
2. **Feeds tab** — filter feeds by title
3. **Feed edit page** — filter feed items by title (without affecting sequence order)

## Key Files

### New
- `src/resources/js/components/search-input.tsx` — reusable search input with debounce, search icon, clear button
- `src/resources/js/hooks/use-debounced-value.ts` — debounce hook (150ms)

### Modified
- `src/resources/js/pages/dashboard.tsx` — add search inputs to Library and Feeds tabs
- `src/resources/js/pages/feeds/edit.tsx` — add search input above item list (filtered view only, sequence preserved)

### Backend
- None. All filtering is client-side on already-loaded data.

## Testing

No backend tests needed. Verify manually:
1. Library: type a partial title → list filters instantly → clear → full list returns
2. Feeds: type a partial feed title → list filters → clear → full list returns
3. Feed edit: type a search → only matching items show → reorder one → clear → order preserved
4. Tab switch: search on Library → switch to Feeds → search is reset
