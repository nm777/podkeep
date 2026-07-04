---

description: "Task list for REST API with API Key Authentication feature"
---

# Tasks: REST API with API Key Authentication

**Input**: Design documents from `/specs/008-rest-api-keys/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: MANDATORY per constitution — all features require test coverage. Tests are written FIRST (Red-Green-Refactor).

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `src/app/Http/`, `src/app/Models/`, `src/app/Services/`, `src/app/Jobs/`
- **Frontend**: `src/resources/js/components/`, `src/resources/js/pages/`, `src/resources/js/hooks/`
- **Tests**: `src/tests/Feature/`, `src/tests/Unit/`
- **Routes**: `src/routes/`
- **Config**: `src/bootstrap/`, `src/config/`, `src/app/Providers/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Install Sanctum and enable API routing

- [X] T001 Run `php artisan install:api --no-interaction` in src/ to install Laravel Sanctum v4, create `src/routes/api.php`, `src/config/sanctum.php`, `src/database/migrations/*_create_personal_access_tokens_table.php`, and scaffold `RateLimiter::for('api')` in `src/app/Providers/AppServiceProvider.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T002 [P] Add `HasApiTokens` trait from Laravel Sanctum to User model in `src/app/Models/User.php` (add `use Laravel\Sanctum\HasApiTokens;` to imports and trait list)
- [X] T003 [P] Create EnsureEligibleForApi middleware in `src/app/Http/Middleware/EnsureEligibleForApi.php` that checks `$user->hasVerifiedEmail()` and `$user->isApproved()`, returning JSON 403 responses (not redirects) — see `specs/008-rest-api-keys/research.md` R3
- [X] T004 Register middleware alias `'eligible.api' => EnsureEligibleForApi::class` in `src/bootstrap/app.php` and verify `install:api` added `api:` routing to `withRouting()`
- [X] T005 [P] Configure `RateLimiter::for('api')` in `src/app/Providers/AppServiceProvider.php` with 60 requests/minute limit keyed by `$request->user()?->id ?: $request->ip()` — see `specs/008-rest-api-keys/research.md` R6
- [X] T006 Create base v1 API route group in `src/routes/api.php` with middleware stack `['throttle:api', 'auth:sanctum', 'eligible.api']` inside `Route::prefix('v1')` group — see `specs/008-rest-api-keys/contracts/authentication.md`

**Checkpoint**: Foundation ready — API routing, Sanctum auth, rate limiting, and eligibility checks are wired. User story implementation can now begin.

---

## Phase 3: User Story 1 — API Key Management (Priority: P1) 🎯 MVP

**Goal**: Approved, verified users can create, list, and revoke named API keys from a settings page linked in the avatar dropdown menu. Keys authenticate subsequent API requests as bearer tokens.

**Independent Test**: Create an API key, verify plaintext is shown once, confirm an API request using that key is authenticated, revoke the key, confirm subsequent requests are rejected.

### Tests for User Story 1 (MANDATORY per constitution) ⚠️

> **NOTE**: Write these tests FIRST, ensure they FAIL before implementation. Tests use Pest PHP and Sanctum's `Sanctum::actingAs()` or `$user->createToken()` helper.

- [X] T007 [P] [US1] Feature test for API key creation in `src/tests/Feature/Api/V1/ApiKeyManagementTest.php` — test POST `/settings/api-keys` creates a key, flash session contains `new_token` plaintext, and the key appears in the database with a hashed token column (see `specs/008-rest-api-keys/contracts/api-keys.md`)
- [X] T008 [P] [US1] Feature test for API key listing in `src/tests/Feature/Api/V1/ApiKeyManagementTest.php` — test GET `/settings/api-keys` returns Inertia page with `tokens` prop containing name, created_at, last_used_at but NEVER the plaintext token
- [X] T009 [P] [US1] Feature test for API key revocation in `src/tests/Feature/Api/V1/ApiKeyManagementTest.php` — test DELETE `/settings/api-keys/{id}` removes the key, revoking another user's key returns 404, and revoked tokens immediately fail authentication
- [X] T010 [P] [US1] Feature test for API authentication in `src/tests/Feature/Api/V1/AuthenticationTest.php` — test requests with valid token return 200, missing token returns 401, invalid token returns 401, revoked token returns 401, unverified user returns 403, unapproved user returns 403 (see `specs/008-rest-api-keys/contracts/authentication.md`)

### Implementation for User Story 1

- [X] T011 [P] [US1] Create StoreApiKeyRequest form request in `src/app/Http/Requests/Settings/StoreApiKeyRequest.php` with validation rule `name: required, string, max:255`
- [X] T012 [US1] Create ApiKeyController in `src/app/Http/Controllers/Settings/ApiKeyController.php` with `index()` (returns Inertia page with `$user->tokens`), `store()` (calls `$user->createToken($name)`, redirects with flash `new_token` plaintext), and `destroy()` (deletes token scoped to `$user->tokens()`) — see `specs/008-rest-api-keys/contracts/api-keys.md`
- [X] T013 [US1] Add API key management routes to `src/routes/settings.php` inside the existing `['auth', 'verified', 'approved']` middleware group: `GET /settings/api-keys` (name `api-keys.index`), `POST /settings/api-keys` (name `api-keys.store`), `DELETE /settings/api-keys/{id}` (name `api-keys.destroy`)
- [X] T014 [P] [US1] Create API Keys settings page in `src/resources/js/pages/settings/api-keys.tsx` using `AppLayout` (matching profile.tsx structure), showing a create form (name input + submit), a list of keys (name, created date, last-used, revoke button), and a one-time plaintext display with copy button when `flash.new_token` is present — use Inertia `useForm` and existing UI components (`Card`, `Button`, `Input`, `Label`, `Heading`)
- [X] T015 [US1] Add "API Keys" DropdownMenuItem to the avatar dropdown in `src/resources/js/components/app-topbar.tsx` (between Password and admin/log-out items) using `route('api-keys.index')` with a `KeyRound` icon from `lucide-react`

**Checkpoint**: User Story 1 is fully functional — users can create and revoke API keys, and those keys authenticate API requests. This is the MVP.

---

## Phase 4: User Story 2 — Podcast Feed Management via API (Priority: P2)

**Goal**: Users can create, list, show, update, and delete podcast feeds via authenticated API endpoints returning JSON resources.

**Independent Test**: Authenticate with an API key, create a feed via `POST /api/v1/feeds`, list feeds to confirm it appears, update its title via `PUT /api/v1/feeds/{id}`, delete it via `DELETE /api/v1/feeds/{id}`.

### Tests for User Story 2 (MANDATORY per constitution) ⚠️

- [X] T016 [P] [US2] Feature test for feed creation in `src/tests/Feature/Api/V1/FeedControllerTest.php` — test POST `/api/v1/feeds` with valid data returns 201 with JSON resource including generated slug/user_guid/token; test validation errors (missing title); test unauthenticated returns 401
- [X] T017 [P] [US2] Feature test for feed listing and show in `src/tests/Feature/Api/V1/FeedControllerTest.php` — test GET `/api/v1/feeds` returns only the authenticated user's feeds as JSON array; test GET `/api/v1/feeds/{id}` returns 404 for another user's feed
- [X] T018 [P] [US2] Feature test for feed update and delete in `src/tests/Feature/Api/V1/FeedControllerTest.php` — test PUT `/api/v1/feeds/{id}` updates fields and returns updated resource; test DELETE returns 204; test modifying another user's feed returns 403/404

### Implementation for User Story 2

- [X] T019 [P] [US2] Create StoreFeedRequest in `src/app/Http/Requests/Api/V1/StoreFeedRequest.php` with rules: `title: required, string, max:255`, `description: nullable, string, max:1000`, `website_url: nullable, url, max:255`, `is_public: boolean` (mirror rules from existing `src/app/Http/Requests/FeedRequest.php` but WITHOUT the `items` array — feed item attachment is a separate endpoint)
- [X] T020 [P] [US2] Create UpdateFeedRequest in `src/app/Http/Requests/Api/V1/UpdateFeedRequest.php` with same rules as StoreFeedRequest but all fields optional
- [X] T021 [US2] Create API FeedController in `src/app/Http/Controllers/Api/V1/FeedController.php` with `index()` (returns `FeedResource::collection($user->feeds()->withCount('items')->latest()->get())`), `store()` (creates feed with auto-generated slug/user_guid/token per existing `FeedController` logic, returns `FeedResource` with 201), `show()`/`update()`/`destroy()` (scoped to `Auth::user()->feeds()` to prevent cross-user access, using `FeedResource` and `Gate::authorize` with existing `FeedPolicy`) — see `specs/008-rest-api-keys/contracts/feeds.md`
- [X] T022 [US2] Register feed API routes in `src/routes/api.php` inside the v1 group: `Route::apiResource('feeds', FeedController::class)` (this provides index/store/show/update/destroy automatically)

**Checkpoint**: User Stories 1 AND 2 both work independently — users can manage feeds via API.

---

## Phase 5: User Story 3 — Media Upload & Library Management via API (Priority: P3)

**Goal**: Users can upload media files (mp3/mp4/m4a/wav/ogg), add media via URL, list library items with processing status, update item metadata, and delete items via the API.

**Independent Test**: Authenticate, upload an mp3 via `POST /api/v1/library`, poll `GET /api/v1/library/{id}` for processing completion, verify it appears in the list, update its title, delete it.

### Tests for User Story 3 (MANDATORY per constitution) ⚠️

- [X] T023 [P] [US3] Feature test for media file upload in `src/tests/Feature/Api/V1/LibraryItemControllerTest.php` — test POST `/api/v1/library` with multipart file upload returns 201 with `processing_status: pending`; test unsupported file type returns 422; test missing title returns 422 (see `specs/008-rest-api-keys/contracts/library.md`)
- [X] T024 [P] [US3] Feature test for media via URL in `src/tests/Feature/Api/V1/LibraryItemControllerTest.php` — test POST `/api/v1/library` with JSON `url` field creates item with `source_type: url` and returns 201
- [X] T025 [P] [US3] Feature test for library listing in `src/tests/Feature/Api/V1/LibraryItemControllerTest.php` — test GET `/api/v1/library` returns user's items with processing status and nested media_file; test GET `/api/v1/library/{id}` returns 404 for another user's item
- [X] T026 [P] [US3] Feature test for library update and delete in `src/tests/Feature/Api/V1/LibraryItemControllerTest.php` — test PUT updates metadata and returns resource; test DELETE returns 204; test modifying another user's item returns 403/404

### Implementation for User Story 3

- [X] T027 [P] [US3] Create StoreLibraryItemRequest in `src/app/Http/Requests/Api/V1/StoreLibraryItemRequest.php` with rules mirroring existing `src/app/Http/Requests/LibraryItemRequest.php`: `title: required, string, max:255`, `description: nullable, string, max:1000`, `file: required_without_all:source_url,url, prohibits:source_url,url, file, mimes:mp3,mp4,m4a,wav,ogg, max:512000`, `url: required_without_all:source_url,file, prohibits:source_url,file, url, max:2048, regex media extension`, `source_url: required_without_all:file,url, prohibits:file,url, url, max:2048`, `feed_ids: nullable, array`, `feed_ids.*: integer, exists in user's feeds`, `published_at: nullable, date`
- [X] T028 [P] [US3] Create UpdateLibraryItemRequest in `src/app/Http/Requests/Api/V1/UpdateLibraryItemRequest.php` with rules: `title: nullable, string, max:255`, `description: nullable, string, max:1000`, `published_at: nullable, date`
- [X] T029 [US3] Create API LibraryItemController in `src/app/Http/Controllers/Api/V1/LibraryItemController.php` with `index()` (returns `LibraryItemResource::collection` with `mediaFile` eager-loaded), `store()` (determines source type, delegates to existing `SourceProcessorFactory::create()` to handle upload/URL/YouTube processing — reusing the same logic as the web `LibraryController` but returning `LibraryItemResource` with 201 instead of redirect), `show()`/`update()`/`destroy()` (scoped to `Auth::user()->libraryItems()`, using `Gate::authorize` with existing `LibraryItemPolicy`, destroy deletes media file if unreferenced) — see `specs/008-rest-api-keys/contracts/library.md`
- [X] T030 [US3] Register library API routes in `src/routes/api.php` inside the v1 group: `Route::apiResource('library', LibraryItemController::class)` and add `->middleware('throttle:10,1')` to the store method to match existing web upload throttling

**Checkpoint**: User Stories 1, 2, AND 3 work independently — users can upload and manage media via API.

---

## Phase 6: User Story 4 — Feed Item Management via API (Priority: P4)

**Goal**: Users can attach library items to feeds, reorder items within a feed, and remove items from feeds via the API.

**Independent Test**: Create a feed and library item via the API, attach the item to the feed via `POST /api/v1/feeds/{id}/items`, reorder via `PUT /api/v1/feeds/{id}/items/reorder`, remove via `DELETE /api/v1/feeds/{id}/items/{itemId}`.

### Tests for User Story 4 (MANDATORY per constitution) ⚠️

- [X] T031 [P] [US4] Feature test for feed item attachment in `src/tests/Feature/Api/V1/FeedItemControllerTest.php` — test POST `/api/v1/feeds/{id}/items` with `library_item_id` creates a feed item with sequence; test attaching another user's library item returns 403; test attaching to another user's feed returns 404 (see `specs/008-rest-api-keys/contracts/feed-items.md`)
- [X] T032 [P] [US4] Feature test for feed item reordering in `src/tests/Feature/Api/V1/FeedItemControllerTest.php` — test PUT `/api/v1/feeds/{id}/items/reorder` with array of `{id, sequence}` updates ordering and returns reordered list; test reordering clears RSS cache
- [X] T033 [P] [US4] Feature test for feed item removal in `src/tests/Feature/Api/V1/FeedItemControllerTest.php` — test DELETE `/api/v1/feeds/{id}/items/{itemId}` removes the pivot record and returns 204; test library item is NOT deleted

### Implementation for User Story 4

- [X] T034 [P] [US4] Create AttachFeedItemRequest in `src/app/Http/Requests/Api/V1/AttachFeedItemRequest.php` with rules: `library_item_id: required, integer` (with closure checking ownership), `sequence: nullable, integer, min:0`
- [X] T035 [P] [US4] Create FeedItemPolicy in `src/app/Policies/FeedItemPolicy.php` with `attach(User $user, Feed $feed, LibraryItem $item): bool` checking `$user->id === $feed->user_id && $user->id === $item->user_id`, and register it in `src/app/Providers/AuthServiceProvider.php` — follows existing policy patterns (`LibraryItemPolicy` style with explicit `: bool` return types)
- [X] T036 [US4] Create API FeedItemController in `src/app/Http/Controllers/Api/V1/FeedItemController.php` with `index()` (list feed items with nested `LibraryItemResource`), `store()` (attach item to feed, default sequence to next available, clear RSS cache), `reorder()` (accept array of `{id, sequence}`, update within transaction, compact sequences, clear RSS cache), `destroy()` (remove pivot record, clear RSS cache) — use `Cache::forget("rss.{$feedId}")` for cache clearing — see `specs/008-rest-api-keys/contracts/feed-items.md`
- [X] T037 [US4] Register feed item API routes in `src/routes/api.php` inside the v1 group: `Route::get('feeds/{feed}/items', [FeedItemController::class, 'index'])`, `Route::post('feeds/{feed}/items', [FeedItemController::class, 'store'])`, `Route::put('feeds/{feed}/items/reorder', [FeedItemController::class, 'reorder'])`, `Route::delete('feeds/{feed}/items/{item}', [FeedItemController::class, 'destroy'])`

**Checkpoint**: User Stories 1-4 work independently — users can fully organize podcast episodes via API.

---

## Phase 7: User Story 5 — Media Processing Operations via API (Priority: P5)

**Goal**: Users can retry failed media processing and trigger redownloads from original source URLs via the API.

**Independent Test**: Create a library item that fails processing, trigger retry via `POST /api/v1/library/{id}/retry`, confirm status changes to pending/processing. Trigger redownload via `POST /api/v1/library/{id}/redownload`.

### Tests for User Story 5 (MANDATORY per constitution) ⚠️

- [X] T038 [P] [US5] Feature test for retry in `src/tests/Feature/Api/V1/MediaProcessingControllerTest.php` — test POST `/api/v1/library/{id}/retry` on a failed item resets status to pending and returns updated resource; test retry on a non-failed item returns error; test retrying another user's item returns 403/404 (see `specs/008-rest-api-keys/contracts/media-processing.md`)
- [X] T039 [P] [US5] Feature test for redownload in `src/tests/Feature/Api/V1/MediaProcessingControllerTest.php` — test POST `/api/v1/library/{id}/redownload` sets status to processing and returns updated resource; test redownload without source_url returns error

### Implementation for User Story 5

- [X] T040 [US5] Create API MediaProcessingController in `src/app/Http/Controllers/Api/V1/MediaProcessingController.php` with `retry()` (load item via `Auth::user()->libraryItems()`, check `hasFailed()`, reset status to pending, delegate to existing `SourceProcessorFactory::create($sourceType)->retry($item)`, return `LibraryItemResource`) and `redownload()` (check `mediaFile` and `source_url` exist, set status to processing, dispatch existing `RedownloadMediaFile` or `ProcessYouTubeAudio` job, return `LibraryItemResource`) — mirror logic from existing `src/app/Http/Controllers/LibraryController.php` retry/redownload methods — see `specs/008-rest-api-keys/contracts/media-processing.md`
- [X] T041 [US5] Register media processing API routes in `src/routes/api.php` inside the v1 group: `Route::post('library/{id}/retry', [MediaProcessingController::class, 'retry'])` and `Route::post('library/{id}/redownload', [MediaProcessingController::class, 'redownload'])`

**Checkpoint**: All user stories (1-5) are now independently functional — the full API workflow is available.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Verification, formatting, and end-to-end validation

- [X] T042 [P] Run end-to-end validation using the workflow in `specs/008-rest-api-keys/quickstart.md` (create key, create feed, upload mp3, poll status, attach to feed, verify RSS)
- [X] T043 [P] Run `vendor/bin/pint --dirty` in src/ to format all new PHP files per project style
- [X] T044 Run full test suite via `php artisan test --no-interaction` in src/ and ensure all existing tests still pass alongside new API tests

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup (T001) completion — BLOCKS all user stories
- **User Stories (Phases 3-7)**: All depend on Foundational phase completion
  - US1 (Phase 3) blocks US2-US5 — API key auth is needed to test API endpoints
  - US2-US5 can proceed in parallel after US1 (if team capacity allows)
  - Or sequentially in priority order (P2 → P3 → P4 → P5)
- **Polish (Phase 8)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) — No dependencies on other stories. **Gating story** — all API endpoint tests require a valid token.
- **User Story 2 (P2)**: Depends on US1 (needs auth to test endpoints). No dependency on US3-US5.
- **User Story 3 (P3)**: Depends on US1 (needs auth). No dependency on US2, US4, US5.
- **User Story 4 (P4)**: Depends on US1 (needs auth). May use feeds (US2) and library items (US3) in tests but can create them independently.
- **User Story 5 (P5)**: Depends on US1 (needs auth). May use library items (US3) in tests but can create them independently.

### Within Each User Story

- Tests MUST be written and FAIL before implementation (Red-Green-Refactor)
- Form requests can be written in parallel (different files)
- Controllers depend on form requests being defined
- Routes depend on controllers existing
- Frontend (US1 only) depends on controller routes being named

### Parallel Opportunities

- T002-T005 (Foundational): All [P] tasks — different files, no dependencies
- T007-T010 (US1 tests): All [P] tasks — can write all test files simultaneously
- T011 and T014 (US1): Form request and frontend page are different files
- T016-T018 (US2 tests): All [P] tasks
- T019-T020 (US2 requests): [P] — different files
- T023-T026 (US3 tests): All [P] tasks
- T027-T028 (US3 requests): [P] — different files
- T031-T033 (US4 tests): All [P] tasks
- T034-T035 (US4 request + policy): [P] — different files
- T038-T039 (US5 tests): [P] — different test scenarios
- After US1 completes, US2-US5 can be worked on in parallel by different developers

---

## Parallel Example: User Story 1

```bash
# Launch all tests for User Story 1 together (all different files or sections):
Task T007: "Feature test for API key creation in src/tests/Feature/Api/V1/ApiKeyManagementTest.php"
Task T008: "Feature test for API key listing (append to same file or separate)"
Task T009: "Feature test for API key revocation (append to same file or separate)"
Task T010: "Feature test for API authentication in src/tests/Feature/Api/V1/AuthenticationTest.php"

# Launch form request and frontend page in parallel (different files):
Task T011: "Create StoreApiKeyRequest in src/app/Http/Requests/Settings/StoreApiKeyRequest.php"
Task T014: "Create API Keys settings page in src/resources/js/pages/settings/api-keys.tsx"
```

## Parallel Example: After US1, Simultaneously Work on US2 + US3

```bash
# Developer A: User Story 2 (Feeds)
Task T016-T018: "Write feed API tests"
Task T019-T022: "Implement feed API endpoints"

# Developer B: User Story 3 (Library)
Task T023-T026: "Write library API tests"
Task T027-T030: "Implement library API endpoints"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001 — `install:api`)
2. Complete Phase 2: Foundational (T002-T006 — trait, middleware, rate limiter, routes)
3. Complete Phase 3: User Story 1 (T007-T015 — API key management + UI)
4. **STOP and VALIDATE**: Create a key, test it authenticates, revoke it, confirm rejection
5. Deploy/demo if ready — users can now create API keys (endpoints come in later stories)

### Incremental Delivery

1. Setup + Foundational → Foundation ready (API routing + Sanctum auth wired)
2. Add User Story 1 → Test independently → Deploy (MVP — users can create/revoke keys)
3. Add User Story 2 → Test independently → Deploy (feeds API available)
4. Add User Story 3 → Test independently → Deploy (media upload API — the primary use case)
5. Add User Story 4 → Test independently → Deploy (feed item management)
6. Add User Story 5 → Test independently → Deploy (processing recovery)
7. Polish → Full validation + formatting → Deploy

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. One developer completes US1 (API key management — gating story)
3. Once US1 is done:
   - Developer A: User Story 2 (Feeds)
   - Developer B: User Story 3 (Library)
   - Developer C: User Story 4 (Feed Items) + User Story 5 (Processing)
4. Stories complete and integrate independently

---

## Notes

- [P] tasks = different files, no dependencies on incomplete tasks
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Verify tests fail before implementing (Red-Green-Refactor)
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Existing API Resources (`FeedResource`, `LibraryItemResource`, `FeedItemResource`, `MediaFileResource`) already exist and are used by API controllers — do NOT recreate them
- Existing policies (`FeedPolicy`, `LibraryItemPolicy`) are reused — only `FeedItemPolicy` is new
- Existing services and jobs are reused — API controllers delegate to `SourceProcessorFactory`, `MediaProcessingService`, `ProcessMediaFile`, etc.
- Run `vendor/bin/pint --dirty` after PHP changes and `npm run lint` after frontend changes
