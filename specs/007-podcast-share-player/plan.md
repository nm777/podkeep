# Implementation Plan: Podcast Share Player Page

**Branch**: `007-podcast-share-player` | **Date**: 2026-05-25 | **Spec**: `specs/007-podcast-share-player/spec.md`

## Summary

Add a public-facing web player page (`/share/{user_guid}/{feed_slug}`) that lets recipients browse episodes and play audio directly in the browser. Access control mirrors the existing RSS/media token pattern: public feeds are open, private feeds require `?token=`. No new database tables required.

## Technical Context

**Language/Version**: PHP 8.4 (Laravel 12), TypeScript (React 19)
**Primary Dependencies**: Laravel Framework, Inertia.js v2, Tailwind CSS v4, Pest PHP v3
**Storage**: MySQL 8.0+, local filesystem (`public` disk) for media
**Testing**: Pest PHP (backend), React Testing Library (frontend)
**Target Platform**: Web application with Docker containerization
**Project Type**: Web application (Laravel backend + React/Inertia frontend)
**Performance Goals**: Page render <500ms, media streaming via existing MediaController
**Constraints**: No new migrations, reuse existing models and access patterns, follow Laravel conventions
**Scale/Scope**: Single new public route + Inertia page, minor update to feed card component

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **API-First**: Controller provides data via Inertia props; frontend consumes it. No separate API needed since this is a read-only public page rendered server-side.
- [x] **Media Processing**: No new media processing. Reuses existing MediaController for streaming.
- [x] **Test-Driven**: Feature tests for ShareController (public access, private+token, invalid token, 404). Frontend tests for player page.
- [x] **Feed Standards**: Not feed-related (no RSS generation). N/A.
- [x] **Security**: Token-based access for private feeds (existing pattern). No auth required for public. Token never exposed to unauthorized users.
- [x] **Performance**: Inertia SSR page, eager-loaded relationships, rate-limited route.

## Project Structure

### Documentation (this feature)

```text
specs/007-podcast-share-player/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
└── contracts/
    └── share-endpoint.md
```

### Source Code (repository root)

```text
src/
├── app/
│   └── Http/
│       └── Controllers/
│           └── ShareController.php          # NEW: share page controller
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── feed-card.tsx                # MODIFIED: add share link button
│   │   │   ├── share-player.tsx             # NEW: inline audio player for share page
│   │   │   └── share-episode-list.tsx       # NEW: episode list for share page
│   │   ├── lib/
│   │   │   └── subscribe-urls.ts           # MODIFIED: add getShareUrl helper
│   │   ├── pages/
│   │   │   └── share/
│   │   │       └── show.tsx                 # NEW: share/player page
│   │   └── types/
│   │       └── index.d.ts                   # MODIFIED: add SharedFeedItem type
│   └── views/
│       └── app.blade.php                    # Unchanged (Inertia root)
├── routes/
│   └── web.php                              # MODIFIED: add share route
└── tests/
    └── Feature/
        └── ShareControllerTest.php          # NEW: feature tests

tests/
├── Feature/
│   └── ShareControllerTest.php
└── Unit/
    └── (no unit tests needed for this feature)
```

**Structure Decision**: Minimal additions to existing Laravel + Inertia structure. One new controller, one new page, three new components, and minor modifications to existing files.

## Complexity Tracking

No constitution violations. All checks pass.
