# Implementation Plan: Search

**Branch**: `013-search` | **Date**: 2026-07-12 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/013-search/spec.md`

## Summary

Add real-time client-side search filtering to three areas: the Library tab (filter library items by title), the Feeds tab (filter feeds by title), and the feed edit page (filter feed items by title). All filtering happens in React state — no backend changes, no new endpoints, no new entities. A shared search input component with debounce handles all three contexts.

## Technical Context

**Language/Version**: PHP 8.4 (Laravel 13), TypeScript (React 19+)  
**Primary Dependencies**: Laravel Framework 13, Inertia.js v3, Tailwind CSS v4, Pest PHP v4  
**Storage**: PostgreSQL (production), SQLite (tests), file storage for media  
**Testing**: Pest PHP v4 (backend), frontend build verification  
**Target Platform**: Web application with Docker containerization  
**Project Type**: Web application (backend API + React frontend via Inertia)  
**Performance Goals**: Client-side filtering < 200ms (instant for already-loaded data)  
**Constraints**: No server round-trips; data already loaded by existing pages; case-insensitive substring match  

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **API-First**: N/A — feature is purely client-side, no new backend endpoints
- [x] **Media Processing**: N/A — no media changes
- [x] **Test-Driven**: Feature tests for search behavior can be added but core logic is client-side filtering
- [x] **Feed Standards**: N/A — no RSS feed changes
- [x] **Security**: N/A — search operates on already-authenticated user's own data
- [x] **Performance**: Client-side filtering meets the < 200ms requirement trivially

## Project Structure

### Documentation (this feature)

```text
specs/013-search/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── quickstart.md        # Phase 1 output
└── tasks.md             # Phase 2 output (not yet created)
```

### Source Code (repository root)

```text
src/
├── resources/js/
│   ├── components/
│   │   └── search-input.tsx       # NEW: reusable search input with debounce
│   ├── hooks/
│   │   └── use-debounced-value.ts # NEW: debounce hook
│   └── pages/
│       └── dashboard.tsx          # Modified: add search to Library + Feeds tabs
│       └── feeds/edit.tsx         # Modified: add search to feed item list
└── tests/
    └── (no backend test changes needed)
```

**Structure Decision**: Minimal — one new component, one new hook, two modified pages. No backend, no models, no controllers.
