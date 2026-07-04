# Phase 0: Research — REST API with API Key Authentication

**Feature**: 008-rest-api-keys
**Date**: 2026-07-03

## Research Tasks

### R1: API Authentication Approach — Sanctum v4 vs Custom Implementation

**Decision**: Use Laravel Sanctum v4 via `php artisan install:api`

**Rationale**:
- The spec requires: create named keys, show plaintext once, store hashed, revoke, track last-used timestamp. This is exactly Sanctum's personal access token feature.
- Sanctum v4 is compatible with Laravel 12 / PHP 8.4.
- The project's boost guidelines explicitly state: *"Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.)"* and *"For APIs, default to using Eloquent API Resources and API versioning."*
- `install:api` scaffolds the migration (`personal_access_tokens`), `config/sanctum.php`, `routes/api.php`, and a `RateLimiter::for('api')` definition in one step — minimal setup overhead.
- Sanctum stores a SHA-256 hash of the token secret (never plaintext), uses `hash_equals()` for timing-safe comparison, and supports per-token expiration and ability scoping.
- Security auditing and maintenance are handled by the Laravel team.
- Token format: `{id}|{40-char-hash}` — the `id` prefix enables O(1) row lookup, then the hash is verified.

**Alternatives considered**:
- **Custom ApiToken model + middleware**: Would provide full control over token format (e.g., `pk_live_` prefixes), per-token rate quotas, and custom fields. However, this requires reimplementing hashing/lookup, timing-safe comparison, ability checks, expiration, pruning, and test helpers (~150-300 LOC). Higher risk of security bugs. No compelling requirement that Sanctum cannot meet.
- **Laravel Passport (OAuth2)**: Overkill for a single-user personal API key system. Introduces OAuth2 complexity (clients, grants, scopes) that does not match the feature's needs.

**Implications**:
- `personal_access_tokens` table uses a polymorphic `tokenable` relationship, but we will only use it with `User`.
- The UI will refer to these as "API Keys" (not "tokens" or "personal access tokens") for user clarity.
- Sanctum's `name`, `last_used_at`, `created_at`, and `expires_at` fields map directly to spec requirements.

---

### R2: API Route Registration in Laravel 12 Streamlined Structure

**Decision**: Use `php artisan install:api` to register `routes/api.php` with the `api` middleware group and `/api` prefix, then add `Route::prefix('v1')` inside for versioning.

**Rationale**:
- Laravel 11+ streamlined structure omits API routing by default. `bootstrap/app.php:14-18` only registers `web` and `commands`.
- `install:api` adds the `api:` argument to `withRouting()`, creating the standard setup.
- The `api` middleware group is stateless: no sessions, no CSRF, automatic JSON error responses. This is correct for a token-based REST API.
- URL versioning (`/api/v1/`) is the simplest and most standard approach. It allows future breaking changes under `/api/v2/` without affecting existing clients.

**Alternatives considered**:
- **Manual route registration** (create `routes/api.php`, add `api:` to `withRouting()` without Sanctum): possible but misses the rate limiter scaffold and Sanctum setup. More manual work for no benefit.
- **Header-based versioning** (`Accept: application/vnd.podkeep.v1+json`): more REST-pure but harder to test and document. Overkill for a personal API.

**Implications**:
- All API routes get the `api` middleware group automatically (stateless).
- The `/api` prefix is applied automatically; we add `/v1` inside the route file.
- Controllers live in `app/Http/Controllers/Api/V1/`.

---

### R3: Verified + Approved Enforcement for Stateless API

**Decision**: Create a new `EnsureApprovedForApi` middleware that returns JSON 403 responses (not redirects) when the authenticated user is unverified, pending, or rejected.

**Rationale**:
- The existing `ApprovedUserMiddleware` (`app/Http/Middleware/ApprovedUserMiddleware.php`) is session-oriented: it calls `auth()->logout()` and `redirect()->route('verification.notice')`, which is inappropriate for a stateless API expecting JSON.
- The spec (FR-017) requires API key management only for approved, verified users — matching the web interface's `['auth', 'verified', 'approved']` middleware stack.
- For the API, `auth:sanctum` already handles authentication (returns 401 JSON for missing/invalid tokens). A separate middleware handles authorization (verified + approved status) and returns 403 JSON.
- Sanctum's `auth:sanctum` guard validates the token and loads the user; our middleware then checks `$user->isApproved()` and `$user->hasVerifiedEmail()`.

**Alternatives considered**:
- **Modify `ApprovedUserMiddleware` to check for JSON**: Adds coupling between web and API concerns. Risk of breaking existing web behavior.
- **Check in each controller**: Violates DRY. Every controller would need the same guard logic.

**Implications**:
- API middleware stack: `['throttle:api', 'auth:sanctum', 'verified.api', 'approved.api']`
- Sanctum's guard already ensures the user exists; our middleware only checks status.
- For token management (web routes in settings), the existing `['auth', 'verified', 'approved']` stack applies unchanged.

---

### R4: API Controller Strategy — Dedicated API Controllers vs Shared Controllers

**Decision**: Create dedicated API controllers in `app/Http/Controllers/Api/V1/` that reuse existing services but return JSON resources instead of redirects.

**Rationale**:
- Existing web controllers (`FeedController`, `LibraryController`) return `RedirectResponse` for Inertia. They cannot serve both HTML redirects and JSON responses cleanly.
- The constitution mandates API-first, but retrofitting existing controllers risks breaking the working UI.
- Dedicated API controllers keep separation of concerns clean: web controllers format for Inertia, API controllers format for JSON.
- Both controller sets reuse the same underlying services (`SourceProcessorFactory`, `MediaProcessingService`, etc.) and jobs (`ProcessMediaFile`, `RedownloadMediaFile`), so business logic is not duplicated.
- API controllers use API-specific form requests (e.g., `StoreFeedRequest` in `app/Http/Requests/Api/V1/`) that may differ slightly from web form requests (e.g., no `items` array on feed creation via API, since feed item attachment is a separate endpoint).

**Alternatives considered**:
- **Add `wantsJson()` branches to existing controllers**: Creates messy conditional logic. Violates single responsibility.
- **Refactor existing controllers to call API controllers**: Larger blast radius. Risks regressions in working UI.

**Implications**:
- 4 new API controllers + 1 new settings controller (ApiKeyController).
- API form requests can share validation rules with web form requests via constants or traits, but are separate classes to allow API-specific customization.

---

### R5: Token (API Key) Management UI Integration

**Decision**: Add an "API Keys" item to the existing avatar dropdown menu in `AppTopbar`, linking to a dedicated `/settings/api-keys` page that uses `AppLayout` directly (matching the profile and password pages).

**Rationale**:
- The avatar dropdown in `app-topbar.tsx` (lines 77-131) already links directly to Profile and Password as separate pages — not via a sidebar layout. Adding "API Keys" as a fourth dropdown item follows the exact same established pattern.
- All three existing settings pages (`profile.tsx`, `password.tsx`, `appearance.tsx`) render directly in `AppLayout` with a `max-w-xl` container — none use a sidebar. The API Keys page matches this structure.
- The dormant `SettingsLayout` (`layouts/settings/layout.tsx`) is not adopted — it is unused dead code and wiring it up would require migrating existing pages and deciding whether it renders `AppTopbar` internally. Simpler to follow the active pattern.
- API keys are inherently per-user, so surfacing them in the user's avatar menu (where Profile and Password already live) is the natural placement.
- Token management routes use the web middleware stack (`['auth', 'verified', 'approved']`) because the user authenticates via session to issue tokens.

**Alternatives considered**:
- **Adopt dormant `SettingsLayout` with sidebar nav**: Would provide a settings sub-nav, but requires migrating 3 existing pages and resolving the layout nesting question (`SettingsLayout` doesn't render `AppTopbar`). Overkill for a 4th settings page when the avatar dropdown pattern already works.
- **Embed in the profile page**: Keeps all user settings in one page, but makes the profile page long. The profile page is a single-column form (`max-w-xl`); adding a key list + create form + copy-once UI would bloat it.

**Implications**:
- New web routes in `routes/settings.php`: `GET /settings/api-keys`, `POST /settings/api-keys`, `DELETE /settings/api-keys/{id}`.
- New controller: `app/Http/Controllers/Settings/ApiKeyController.php`.
- New Inertia page: `resources/js/pages/settings/api-keys.tsx` (uses `AppLayout`, `max-w-xl` container).
- New `<DropdownMenuItem>` in `app-topbar.tsx` avatar dropdown (between Password and the admin/log out items), using `route('api-keys.index')` and a key icon from `lucide-react`.

---

### R6: Rate Limiting Strategy

**Decision**: Use `RateLimiter::for('api')` keyed by user ID (when authenticated) or IP, with a limit of 60 requests/minute. Apply `throttle:api` to all API routes.

**Rationale**:
- `install:api` scaffolds a `RateLimiter::for('api')` definition in `AppServiceProvider::boot()` keyed by `$request->user()?->id ?: $request->ip()`. This is the standard Laravel API convention.
- 60 requests/minute is generous enough for normal API usage (including polling media processing status) while preventing abuse.
- The existing app uses throttle middleware on web routes (e.g., `throttle:10,1` for library uploads, `throttle:120,1` for RSS). The API rate limit is a separate, higher ceiling.
- Library upload endpoint keeps its existing `throttle:10,1` constraint layered on top, matching the web behavior.

**Alternatives considered**:
- **Per-token rate limiting** (keyed by `currentAccessToken()->id`): More granular but adds complexity. Can be added later if needed.
- **No rate limiting**: Violates FR-015 and leaves the API open to abuse.

**Implications**:
- Over-limit responses return HTTP 429 with `Retry-After` and `X-RateLimit-*` headers automatically.
- The rate limiter is registered in `AppServiceProvider::boot()`.

---

### R7: JSON Error Response Format

**Decision**: Rely on Laravel's automatic JSON rendering for `api` routes. No custom error format needed.

**Rationale**:
- Laravel automatically renders exceptions as JSON when the request uses the `api` middleware group or sends `Accept: application/json`.
- Standard error responses: 401 (unauthenticated), 403 (forbidden/unapproved), 404 (not found), 422 (validation), 429 (rate limited).
- The default JSON shape (`{"message": "...", "errors": {...}}`) is sufficient and matches Laravel conventions.
- Sanctum's `auth:sanctum` middleware returns 401 JSON for missing/invalid tokens automatically.

**Alternatives considered**:
- **Custom error envelope** (e.g., `{"error": {"code": "...", "message": "..."}}`): More structured but diverges from Laravel defaults. Adds complexity without clear benefit for a personal API.
- **RFC 7807 Problem Details**: Standardized but heavy. Not warranted for this scope.

**Implications**:
- No custom exception rendering needed in `bootstrap/app.php`.
- Validation errors from form requests automatically return 422 JSON with field-level errors.

---

## Summary of Decisions

| # | Question | Decision |
|---|----------|----------|
| R1 | Auth approach | Laravel Sanctum v4 via `install:api` |
| R2 | Route registration | `install:api` + `/api/v1` prefix |
| R3 | Approved/verified enforcement | New `EnsureApprovedForApi` middleware (JSON 403) |
| R4 | Controller strategy | Dedicated API controllers in `Api\V1\` |
| R5 | Token management UI | Avatar dropdown menu item + dedicated settings page in `AppLayout` |
| R6 | Rate limiting | `RateLimiter::for('api')`, 60 req/min, throttle:api |
| R7 | Error format | Laravel automatic JSON rendering (no custom format) |
