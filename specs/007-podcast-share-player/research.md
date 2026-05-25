# Research: Podcast Share Player Page

## Research Tasks

### 1. Access Control Pattern for Share Pages

**Decision**: Reuse existing feed token pattern from RssController and MediaController.

**Rationale**: The codebase already implements a consistent token-based access control pattern:
- `RssController::show()` checks `$feed->is_public` and validates `$request->token === $feed->token` for private feeds
- `MediaController::show()` checks `?feed_token=` query param against feed tokens
- Both return 404 on access denied (no information leakage)

The share page should follow this exact pattern for consistency. No new token infrastructure needed.

**Alternatives Considered**:
- Separate share tokens per feed: Rejected — adds migration complexity, feed token already serves this purpose
- Signed URLs: Rejected — Laravel signed URLs expire, RSS subscribers need persistent access, inconsistent with existing pattern
- Session-based access: Rejected — share links must work without login

### 2. Media URL Construction for Private Feeds

**Decision**: Append `?feed_token={token}` to media URLs when the feed is private, consistent with MediaController's existing `feed_token` query parameter.

**Rationale**: `MediaController::show()` already checks for `?feed_token=` (line 19, 37-49). The share page just needs to construct URLs with this parameter when the feed is private. The token is already available from the request (user accessed the share page with `?token=xxx`).

**Alternatives Considered**:
- Proxy media through share controller: Rejected — unnecessary complexity, MediaController already handles this
- Signed media URLs: Rejected — doesn't match existing pattern

### 3. Page Rendering Approach

**Decision**: Use Inertia.js to render a standalone page (no app shell/sidebar layout).

**Rationale**: The share page is public-facing and should not show the app's dashboard sidebar/header. It needs its own minimal layout. The existing `media-player.tsx` component pattern shows how audio is handled. The page should be SSR-compatible for link previews.

**Alternatives Considered**:
- Blade template with inline HTML/JS: Rejected — inconsistent with Inertia.js architecture
- Separate SPA route: Rejected — unnecessary, Inertia handles this naturally
- Embeddable iframe widget: Rejected — out of scope, full page is requested

### 4. Audio Player Implementation

**Decision**: Use native HTML5 `<audio>` element with controls, consistent with existing `media-player.tsx`.

**Rationale**: The existing `media-player.tsx` already uses `<audio>` with `controls` and `preload="metadata"`. The share page should use the same approach but in a non-modal, inline layout. No need for a custom player UI — native controls are accessible and familiar.

**Alternatives Considered**:
- Custom audio player with Waveform: Rejected — unnecessary complexity for v1
- Third-party player library (Howler.js, etc.): Rejected — adds dependency, native `<audio>` is sufficient
- Persistent bottom player bar: Rejected — can be added later, inline per-episode is simpler

### 5. Feed Data Loading

**Decision**: Eager load `items.libraryItem.mediaFile` in a single query, same as RssController.

**Rationale**: `RssController::show()` already uses `->with(['items.libraryItem.mediaFile'])` (line 17). This avoids N+1 queries and is the established pattern. The share controller should use the identical query.

**Alternatives Considered**:
- Paginated items: Rejected — most feeds are small; pagination can be added later if needed
- Lazy loading: Rejected — N+1 risk

### 6. Route Design

**Decision**: `GET /share/{user_guid}/{feed_slug}` with optional `?token=` for private feeds.

**Rationale**: Mirrors the RSS route pattern (`/rss/{user_guid}/{feed_slug}`) exactly. Uses the same URL identifiers (`user_guid` + `slug`) so the same share link structure is familiar. The route is added outside auth middleware groups.

**Alternatives Considered**:
- `/podcast/{user_guid}/{feed_slug}`: Rejected — "share" is more accurate, avoids confusion with RSS feed
- `/s/{short_code}`: Rejected — would require new short_code field/migration
- `/feeds/{id}/share`: Rejected — exposes internal ID, inconsistent with existing URL patterns

### 7. Feed Card Update

**Decision**: Add a "Copy Share Link" button to the existing `feed-card.tsx` component, using a `Share` icon from lucide-react.

**Rationale**: The feed card already has copy RSS URL, Apple Podcasts, and Google Podcasts buttons. Adding a share link button follows the same tooltip pattern. A new `getShareUrl()` helper in `subscribe-urls.ts` generates the URL.

**Alternatives Considered**:
- Separate share button in feed edit page: Rejected — feed card is the primary interaction point
- Modal with multiple share options: Rejected — over-engineered for v1
