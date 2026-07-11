# Implementation Plan: Stable Podcast Links (Links Survive Renames)

**Branch**: `011-stable-podcast-links` | **Date**: 2026-07-10 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/011-stable-podcast-links/spec.md`

## Summary

Renaming a podcast (or saving any edit) currently regenerates the public URL slug from the title, silently breaking every previously-shared RSS and share-page link — including podcast-app subscriptions, which treat their cached RSS URL as permanent. The fix makes the slug **write-once**: it is generated from the title at creation and never touched again. This is a one-line removal of the slug regeneration in the web `FeedController::update()` (the API path already leaves the slug untouched), plus tests asserting slug stability across renames and that the original RSS/share URLs keep resolving while the feed reflects the new title. No migration, no schema change, no frontend change is required.

## Technical Context

**Language/Version**: PHP 8.4 (Laravel 12), TypeScript (React 19)
**Primary Dependencies**: Laravel Framework, Inertia.js v2, Tailwind CSS v4, Pest PHP v3, Laravel Sanctum v4 (personal access tokens for API)
**Storage**: MySQL 8.0+ (SQLite for tests), database-backed queues, local `public` disk for media files
**Testing**: Pest PHP v3 (backend), feature tests first (TDD)
**Target Platform**: Web application with Docker containerization
**Project Type**: Web application (backend API + React frontend via Inertia)
**Performance Goals**: API responses <500ms, feed generation <5s
**Constraints**: Follow Laravel conventions; public podcast URLs must be permanent (podcast-industry standard — podcatchers cache the RSS URL and a change severs the subscription); 90% test coverage
**Scale/Scope**: Single bug-fix-class feature. Touches one controller method; no new tables, no migrations, no frontend.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **API-First**: Feature is backend-only. The web controller is the root cause; the API `FeedController::update()` already leaves the slug untouched. Backend change first and only — no frontend component is needed.
- [x] **Media Processing**: N/A — feature involves no media processing. No change to queued jobs.
- [x] **Test-Driven**: Tests asserting slug stability and link survival will be written before/with the fix (see quickstart.md).
- [x] **Feed Standards**: RSS feed remains valid after rename — feed content is driven by `$feed->title` (which still updates); the RSS output cache is keyed by `feed->id` and is cleared on update (`FeedController.php:96`), so it regenerates with the new title. The slug is no longer mutated, so the public RSS URL is stable.
- [x] **Security**: Authorization already enforced via `FeedPolicy::update` (owner-only) on the web path and `Auth::user()->feeds()->findOrFail($id)` ownership scoping on the API path. No change to trust boundaries.
- [x] **Performance**: Strictly improves — removes the `generateUniqueSlug()` DB probe loop that currently ran on **every** feed save (even unrelated edits). Renames now perform zero extra queries.

## Project Structure

### Documentation (this feature)

```text
specs/011-stable-podcast-links/
├── plan.md              # This file
├── research.md          # Phase 0 — immutable-slug decision and alternatives
├── data-model.md        # Phase 1 — Feed.slug write-once semantic
├── quickstart.md        # Phase 1 — verification steps
├── contracts/
│   ├── public-url.md    # RSS/share public URL contract (slug is permanent)
│   └── api-feed.md      # API v1 feed update contract (slug not accepted)
└── tasks.md             # Phase 2 output (/speckit.tasks — NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
src/
├── app/
│   └── Http/Controllers/
│       ├── FeedController.php            # MODIFY: stop regenerating slug in update()
│       └── Api/V1/FeedController.php     # NO CHANGE (already leaves slug untouched)
└── tests/
    └── Feature/
        └── StableFeedLinksTest.php       # NEW: slug-stability + link-survival tests
```

**Structure Decision**: Existing structure reused — no new directories. The change is localized to the web `FeedController::update()` method. Tests live alongside the other feature tests under `src/tests/Feature/`, following the Pest conventions already used by `FeedEditTest.php` and `FeedManagementTest.php`.

## Complexity Tracking

> No Constitution Check violations. Nothing to justify.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| —          | —          | —                                    |
