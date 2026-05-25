# Tasks: Podcast Share Player Page

**Input**: Design documents from `/specs/007-podcast-share-player/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: MANDATORY per constitution - all features require test coverage.

**Organization**: Tasks grouped by user story for independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `src/app/Http/Controllers/`, `src/app/Models/`, `src/app/Services/`
- **Frontend**: `src/resources/js/components/`, `src/resources/js/pages/`, `src/resources/js/lib/`, `src/resources/js/types/`
- **Routes**: `src/routes/`
- **Tests**: `src/tests/Feature/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: TypeScript types and utility helpers needed across all user stories

- [x] T001 [P] Add `ShareFeed`, `ShareEpisode`, and `SharePageProps` types to `src/resources/js/types/index.d.ts`
- [x] T002 [P] Add `getShareUrl(feed: Feed): string` helper to `src/resources/js/lib/subscribe-urls.ts`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Backend route and controller that all user stories depend on

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T003 Add share route `GET /share/{user_guid}/{feed_slug}` with `throttle:120,1` middleware to `src/routes/web.php`
- [x] T004 Create `ShareController` with `show()` method in `src/app/Http/Controllers/ShareController.php` — finds feed by `user_guid` + `slug`, eager-loads `items.libraryItem.mediaFile`, filters to completed items with media, maps episodes to `{ id, title, description, published_at, duration, media_url }`, constructs `media_url` with `?feed_token=` for private feeds, returns `Inertia::render('share/show', [...props])`
- [x] T005 Write feature tests for `ShareController` in `src/tests/Feature/ShareControllerTest.php` — test: public feed returns 200, non-existent feed returns 404, private feed without token returns 404, private feed with valid token returns 200, private feed with wrong token returns 404, only completed items with media shown, episodes ordered by sequence ascending

**Checkpoint**: Backend endpoint ready — user story frontend work can begin

---

## Phase 3: User Story 1 — Share Page with Player for Public Feeds (Priority: P1) 🎯 MVP

**Goal**: Recipients can open a share link for a public feed, see all episodes listed, and play audio directly in the browser

**Independent Test**: Visit `/share/{guid}/{slug}` for a public feed with completed items — page renders with feed title, episode list, and playable audio

### Implementation for User Story 1

- [x] T006 [US1] Create `SharePlayer` component in `src/resources/js/components/share-player.tsx` — native HTML5 `<audio>` element with `controls` and `preload="metadata"`, accepts `mediaUrl` and `title` props, displays episode title above player, dark mode support
- [x] T007 [US1] Create `ShareEpisodeList` component in `src/resources/js/components/share-episode-list.tsx` — renders list of `ShareEpisode` items, each showing title, description (truncated), published date, formatted duration, and play button that sets active episode in parent state
- [x] T008 [US1] Create share page in `src/resources/js/pages/share/show.tsx` — standalone layout (no app sidebar/header), displays feed cover image/title/description, renders `ShareEpisodeList`, renders `SharePlayer` for the currently selected episode, dark mode support, responsive mobile-first layout

**Checkpoint**: Public share page fully functional — visit `/share/{guid}/{slug}` and play episodes

---

## Phase 4: User Story 2 — Private Feed Token Access (Priority: P2)

**Goal**: Private feeds require `?token=` parameter for web access, consistent with RSS and media token patterns

**Independent Test**: Visit `/share/{guid}/{slug}` for a private feed — returns 404 without token, returns 200 with valid `?token=`, media URLs include `?feed_token=` parameter

### Tests for User Story 2

- [x] T009 [US2] Add private feed token tests to `src/tests/Feature/ShareControllerTest.php` — verify private feed media URLs contain `feed_token` query param, verify RSS URL in props includes token for private feeds, verify public feed media URLs do not contain `feed_token`

### Implementation for User Story 2

- [x] T010 [US2] Update `ShareController::show()` in `src/app/Http/Controllers/ShareController.php` — ensure access check uses `$feed->is_public` and `$request->token === $feed->token` pattern from RssController, ensure `media_url` includes `?feed_token={token}` only for private feeds, ensure `rssUrl` prop includes `?token=` only for private feeds

**Note**: If T004 already implements the full access control (public + private), T010 becomes a verification pass to ensure correctness. T009 tests should pass.

**Checkpoint**: Private feeds accessible via token, media streaming works for private episodes

---

## Phase 5: User Story 3 — Copy Buttons (Priority: P3)

**Goal**: Feed owners can copy the share link from the dashboard; recipients can copy the RSS URL from the share page

**Independent Test**: Click "Copy Share Link" on feed card — copies `/share/...` URL. Click "Copy RSS URL" on share page — copies RSS URL.

### Implementation for User Story 3

- [x] T011 [P] [US3] Add "Copy Share Link" button with `Share` icon to `src/resources/js/components/feed-card.tsx` — uses `getShareUrl()` from subscribe-urls.ts, copies absolute URL to clipboard, follows existing Tooltip pattern from "Copy RSS URL" button
- [x] T012 [US3] Add "Copy RSS URL" button to share page in `src/resources/js/pages/share/show.tsx` — uses `rssUrl` prop from controller, copies to clipboard with visual feedback

**Checkpoint**: Share link copyable from dashboard, RSS URL copyable from share page

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Final quality checks across all user stories

- [x] T013 Run `vendor/bin/pint --dirty` to fix PHP code style
- [x] T014 Run `php artisan test` to verify all tests pass
- [x] T015 [P] Run `npm run lint` to verify TypeScript/React code style
- [x] T016 Verify dark mode renders correctly on share page
- [x] T017 Verify mobile responsive layout on share page

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: No dependency on Phase 1 (backend is independent of frontend types) — can start in parallel
- **US1 (Phase 3)**: Depends on Phase 1 (types) and Phase 2 (controller/route)
- **US2 (Phase 4)**: Depends on Phase 2 (controller must exist for token tests)
- **US3 (Phase 5)**: Depends on Phase 1 (subscribe-urls helper) and Phase 3 (share page exists)
- **Polish (Phase 6)**: Depends on all user stories complete

### User Story Dependencies

- **US1 (P1)**: Depends on Phase 1 + Phase 2 — no dependencies on other stories
- **US2 (P2)**: Depends on Phase 2 — tests private feed behavior of the same controller
- **US3 (P3)**: Depends on Phase 1 + US1 share page — adds copy buttons

### Within Each User Story

- Tests written before implementation (T005 before T004, T009 before T010)
- Components before page composition (T006, T007 before T008)
- Backend before frontend (Phase 2 before Phase 3)

### Parallel Opportunities

- T001 and T002 (Phase 1) — different files, run in parallel
- T001/T002 and T003/T004 (Phase 1 + Phase 2) — backend and frontend types are independent
- T006 and T007 (US1 components) — different component files, run in parallel
- T009 and T011 (US2 test + US3 feed card) — different files, run in parallel
- T013, T015 (Polish lint checks) — different tools, run in parallel

---

## Parallel Example: Phase 1 + Phase 2

```
Task T001: "Add ShareFeed, ShareEpisode, SharePageProps types to src/resources/js/types/index.d.ts"
Task T002: "Add getShareUrl helper to src/resources/js/lib/subscribe-urls.ts"
Task T003: "Add share route to src/routes/web.php"
```

## Parallel Example: User Story 1

```
Task T006: "Create SharePlayer component in src/resources/js/components/share-player.tsx"
Task T007: "Create ShareEpisodeList component in src/resources/js/components/share-episode-list.tsx"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (types + utility)
2. Complete Phase 2: Foundational (controller + route + tests)
3. Complete Phase 3: User Story 1 (share page with player)
4. **STOP and VALIDATE**: Visit `/share/{guid}/{slug}` for a public feed, verify episodes list and audio playback
5. Deploy/demo if ready

### Incremental Delivery

1. Setup + Foundational → Backend endpoint ready
2. Add User Story 1 → Public share page works (MVP!)
3. Add User Story 2 → Private feeds work with tokens
4. Add User Story 3 → Copy buttons on dashboard + share page
5. Polish → Code style, lint, responsive, dark mode verification

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- No new migrations needed — reuses existing Feed/FeedItem/LibraryItem/MediaFile models
- Access control mirrors RssController pattern exactly
- Audio player uses native HTML5 `<audio>` (same as existing media-player.tsx)
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
