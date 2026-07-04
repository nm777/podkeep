# Implementation Plan: REST API with API Key Authentication

**Branch**: `008-rest-api-keys` | **Date**: 2026-07-03 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/008-rest-api-keys/spec.md`

## Summary

Add a REST API layer to podkeep authenticated via personal API keys (bearer tokens). Users create and revoke keys from a new Settings > API Keys page, then use those keys to perform all UI-accessible operations — feed CRUD, library item upload/management, feed item attachment/reordering, and media processing retry/redownload — through JSON API endpoints. Implementation uses Laravel Sanctum v4 for token management, a new `routes/api.php` with `/api/v1` versioning, dedicated API controllers returning Eloquent API Resources, and reuses existing media processing services.

## Technical Context

**Language/Version**: PHP 8.4 (Laravel 12), TypeScript (React 19)
**Primary Dependencies**: Laravel Framework, Inertia.js v2, Tailwind CSS v4, Pest PHP v3, Laravel Sanctum v4 (NEW — for personal access tokens)
**Storage**: MySQL 8.0+ (SQLite for tests), database-backed queues, local `public` disk for media files
**Testing**: Pest PHP v3 (backend), feature/integration tests required
**Target Platform**: Web application with Docker containerization
**Project Type**: Web application (session-based UI + new stateless REST API)
**Performance Goals**: API responses <500ms (cached), media processing <10min, error responses <2s
**Constraints**: 90% test coverage, RSS 2.0 compliance, API routes must be stateless (no session/CSRF), must enforce verified+approved user status on API requests
**Scale/Scope**: Personal podcast management API — single-user-scoped resources, no multi-tenant complexity

**Key existing assets to reuse**:
- 4 existing (unused) API Resources: `FeedResource`, `LibraryItemResource`, `FeedItemResource`, `MediaFileResource` in `app/Http/Resources/`
- Existing services: `SourceProcessorFactory`, `MediaProcessingService`, duplicate detection
- Existing jobs: `ProcessMediaFile`, `ProcessYouTubeAudio`, `RedownloadMediaFile`, `AddLibraryItemToFeedsJob`
- Existing policies: `FeedPolicy`, `LibraryItemPolicy` (ownership-based)
- Existing form request validation rules (to be shared or mirrored for API)
- Existing avatar dropdown in `AppTopbar` component (Profile, Password items — add API Keys alongside)

**Unknowns resolved in Phase 0**:
- Auth approach: Sanctum v4 vs custom (resolved → Sanctum)
- API route registration method in Laravel 12 (resolved → `install:api`)
- Approved/verified enforcement for stateless API (resolved → custom API middleware)
- API versioning strategy (resolved → URL prefix `/api/v1`)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **API-First**: This feature IS the API implementation. New API controllers are created before any frontend changes. API endpoints return JSON resources; the Settings UI consumes web routes (session-auth) for token management only.
- [x] **Media Processing**: API upload endpoints reuse existing async processing pipeline (`SourceProcessorFactory` → queued jobs). No synchronous processing added.
- [x] **Test-Driven**: Feature tests will be written first for every API endpoint (auth, feeds, library, feed items, processing). Tests use Sanctum's `actingAs()` helper for API auth.
- [x] **Feed Standards**: RSS feeds unaffected — API only manages feed/library data, does not alter RSS generation. Existing RSS caching/compliance preserved.
- [x] **Security**: Bearer token auth via Sanctum (hashed storage, plaintext shown once). Ownership enforced via existing policies + Gate authorization. Rate limiting via `RateLimiter::for('api')`. Verified+approved status checked via API-compatible middleware.
- [x] **Performance**: API responses target <500ms. Rate limiting prevents abuse. Existing async media processing keeps upload endpoints responsive.

## Project Structure

### Documentation (this feature)

```text
specs/008-rest-api-keys/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   ├── authentication.md
│   ├── api-keys.md
│   ├── feeds.md
│   ├── library.md
│   ├── feed-items.md
│   └── media-processing.md
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
src/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── V1/
│   │   │   │       ├── FeedController.php          # API feed CRUD
│   │   │   │       ├── LibraryItemController.php   # API library CRUD + upload
│   │   │   │       ├── FeedItemController.php      # API feed item attach/remove/reorder
│   │   │   │       └── MediaProcessingController.php # API retry/redownload
│   │   │   └── Settings/
│   │   │       └── ApiKeyController.php            # Web UI token create/list/revoke
│   │   ├── Middleware/
│   │   │   └── EnsureApprovedForApi.php            # API-compatible approved check (returns JSON, not redirect)
│   │   ├── Requests/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── StoreFeedRequest.php
│   │   │           ├── UpdateFeedRequest.php
│   │   │           ├── StoreLibraryItemRequest.php
│   │   │           ├── UpdateLibraryItemRequest.php
│   │   │           ├── AttachFeedItemRequest.php
│   │   │           └── StoreApiKeyRequest.php
│   │   └── Resources/  (existing: FeedResource, LibraryItemResource, FeedItemResource, MediaFileResource)
│   ├── Models/
│   │   └── User.php    # Add HasApiTokens trait
│   ├── Policies/
│   │   └── FeedItemPolicy.php  # NEW — authorization for feed item management
│   └── Providers/
│       └── AppServiceProvider.php  # RateLimiter::for('api')
├── routes/
│   ├── api.php          # NEW — /api/v1 routes (created by install:api)
│   └── settings.php     # Add API key management routes
├── resources/js/
│   ├── pages/settings/
│   │   └── api-keys.tsx # NEW — API key management page (uses AppLayout directly)
│   └── components/
│       └── app-topbar.tsx # Add 'API Keys' item to avatar dropdown menu
├── database/
│   └── migrations/
│       └── *_create_personal_access_tokens_table.php  # Created by install:api
└── bootstrap/
    └── app.php          # Enable api routing, alias Sanctum middleware

tests/
├── Feature/
│   └── Api/
│       └── V1/
│           ├── AuthenticationTest.php
│           ├── FeedControllerTest.php
│           ├── LibraryItemControllerTest.php
│           ├── FeedItemControllerTest.php
│           ├── MediaProcessingControllerTest.php
│           └── ApiKeyManagementTest.php
└── Pest.php
```

**Structure Decision**: API controllers live in `app/Http/Controllers/Api/V1/` following Laravel's versioned API convention. API-specific form requests live in `app/Http/Requests/Api/V1/`. The existing web controllers (`FeedController`, `LibraryController`) remain unchanged — they serve the Inertia UI. API controllers reuse the same services and jobs but return JSON resources instead of redirects. Token management uses web routes (session-auth) since the UI must authenticate via session to issue tokens; the issued tokens then authenticate stateless API calls. The API Keys settings page uses `AppLayout` directly (matching the profile/password pages) and is linked from a new item in the avatar dropdown in `AppTopbar`.
