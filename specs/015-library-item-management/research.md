# Phase 0 Research: Improved Library Item Management

**Branch**: `015-library-item-management` | **Date**: 2026-07-24

No NEEDS CLARIFICATION markers were carried over from the spec — all ambiguous points were resolved with informed guesses documented as assumptions. This file records the technical decisions behind those guesses and the integration patterns discovered during codebase research, so `/speckit.tasks` has everything it needs.

## R1 — Where to enforce "hidden feeds" filtering (picker only, not dashboard)

**Decision**: Filter hidden feeds **client-side** in `dashboard.tsx`, at the point feeds are passed to `MediaUploadButton`. Do NOT filter server-side in the shared Inertia `feeds` prop.

**Rationale**: The shared `feeds` prop is produced once in `HandleInertiaRequests::share()` (`src/app/Http/Middleware/HandleInertiaRequests.php:55`) as `$request->user()->feeds()->latest()->get()`. The dashboard's **Feeds tab** (`FeedCard` list in `dashboard.tsx:161`) consumes that same `feeds` array and MUST continue to show every feed (FR-008: hiding affects only the picker). If we filtered server-side, the dashboard feed list would also lose the feeds. Filtering at the `MediaUploadButton feeds={…}` prop is one line and leaves the shared prop authoritative.

**Alternatives considered**:
- *Filter inside `FeedSelector`*: also viable, but `FeedSelector` is a generic presentational component; pushing domain knowledge ("hidden feeds") into it couples it to this feature. Filtering at the call site in `dashboard.tsx` keeps `FeedSelector` dumb. (If `MediaUploadButton` later appears elsewhere, move the filter into `MediaUploadButton` — but today it has a single caller.)
- *Separate server prop `selectableFeeds`*: over-engineering; doubles the shared query and splits one source of truth. Rejected.

## R2 — Column naming and default for the hide flag

**Decision**: Add `is_hidden_from_selector` BOOLEAN NOT NULL DEFAULT 0 (i.e. default **false = shown**), matching FR-003.

**Rationale**: The spec requirement is "defaults to shown," and the existing feed convention uses default-false-positive booleans (`is_public` default false). A `false = shown / true = hidden` column is the smallest change. The UI checkbox will be labeled positively ("Show in Add Media list", checked = shown) and invert to the column on save, which is the normal pattern already used for `is_public`.

**Alternatives considered**:
- *Positive column `show_in_media_picker` default true*: avoids a double-negative but diverges from the `is_*` naming convention on the table and makes the migration's default read awkwardly. Rejected for consistency.
- *A separate "feed visibility" enum*: speculative generality for a single binary. YAGNI.

## R3 — Search scope for the "Add Media" tab (client-side vs server-side)

**Decision**: Client-side, title-substring filter, reusing `SearchInput` + `useDebouncedValue`, identical to the existing feeds-list search (`dashboard.tsx:40`) and the existing feed-items search (`feeds/edit.tsx:69`).

**Rationale**: The spec explicitly says "searchable like the feed list is today," and the feed list search is client-side. Consistency of UX and code wins. The backend change required is only to **stop capping** `userLibraryItems` at 100 (`FeedController::edit`, `src/app/Http/Controllers/FeedController.php:70`) so the whole personal library is available to filter.

**Ceiling / upgrade path** (`ponytail:` style): loading the entire library per edit-page visit is O(n) in memory and transfer. For a single-user personal podcast app this is fine up to a few thousand items. If a user's library grows large enough that the edit page payload is slow, the upgrade path is an `/api/library-items/search?q=` endpoint with debounced server-side `LIKE` (or full-text) and the same `SearchInput` debounce. That is deferred — not built now.

**Alternatives considered**:
- *Server-side search from the start*: more moving parts (route, controller method, request validation, debounce wiring) for a problem that doesn't exist at personal-library scale. Violates ponytail. Rejected unless metrics prove otherwise.

## R4 — Tabbed layout for the feed editor

**Decision**: Introduce a lightweight two-tab switch within the existing "Feed Items" section of `feeds/edit.tsx`: **"Feed Items"** (current items — search, drag-reorder, remove, already built) and **"Add Media"** (available library items — new searchable, tall list). Local React `useState` for the active tab; no routing change.

**Rationale**: The spec asks to "tab back and forth between the list of items on the feed and the list of other media." A local state tab is the minimal implementation. The project has no shared Tabs primitive in `resources/js/components/ui/`; rather than add a dependency or build a reusable Tabs component for a single two-tab use case, inline tab buttons (mirroring the existing dashboard tab pattern at `dashboard.tsx:100`) are the lazy, consistent choice.

**Alternatives considered**:
- *Build a reusable `<Tabs>` component*: speculative abstraction with one consumer. YAGNI until a second caller appears.
- *Add shadcn/ui Tabs*: pulls in Radix dependency the project doesn't currently use. Rejected.

## R5 — "Add Media" list height / virtualization

**Decision**: Replace `max-h-48` with a substantially taller scroll container (e.g. `max-h-[60vh]`), no virtualization.

**Rationale**: FR-006 wants the list "substantially taller." A viewport-relative max-height gives a usable, scrollable list without the complexity of virtualization. Virtualization is only justified at thousands of visible rows, which R3 already defers via server-side search.

**Alternatives considered**:
- *Virtualized list (`@tanstack/react-virtual` etc.)*: premature; the library isn't that large, and the DOM cost of a few hundred simple rows is negligible. Rejected.

## R6 — Processing items in the "Add Media" list

**Decision**: Include library items whose media is still processing (no `media_file` yet), labeled "Processing…" exactly as `LibraryItemInfo` already does (`feeds/edit.tsx:13`). No special filtering.

**Rationale**: Edge case in the spec. Users routinely queue a source into a feed before processing finishes (the add-media dialog already attaches to feeds "after processing completes"). Hiding them would be surprising.

## R7 — Authorization & validation for the new field

**Decision**: Add `'is_hidden_from_selector' => ['boolean']` to `FeedRequest::rules()`. Persist via the existing `$feed->update([...])` in `FeedController@update` and `Auth::user()->feeds()->create([...])` in `@store`. No new policy — `Gate::authorize('update', $feed)` already guards edit/update.

**Rationale**: The field is a harmless boolean owned by the feed's user; existing ownership (`feeds()->create`) and authorization gates already scope it. Boolean validation prevents odd types. No security boundary is added or relaxed.

## R8 — FeedFactory / test data

**Decision**: Ensure the `FeedFactory` exposes `is_hidden_from_selector` (default false) so tests can use `['is_hidden_from_selector' => true]` states. Verify during implementation; add only if missing.

**Rationale**: TDD requires a clean way to manufacture hidden feeds. Factory is the project convention (constitution: "Database factories required for all models").

---

**Resolved NEEDS CLARIFICATION count**: 0 (none carried in; all decisions above default to the documented assumptions in `spec.md`). Ready for Phase 1.
