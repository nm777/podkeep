# Contract: Inertia Page Props & Picker Behavior

**Branch**: `015-library-item-management` | **Date**: 2026-07-24

Covers the two Inertia pages this feature touches and the exact client-side filtering contract for the add-media picker.

## Shared `feeds` prop (all pages)

**Source:** `App\Http\Middleware\HandleInertiaRequests::share()` — `src/app/Http/Middleware/HandleInertiaRequests.php:55`.

```php
'feeds' => Inertia::defer(fn (): array => $request->user()
    ? $request->user()->feeds()->latest()->get()->toArray()
    : []),
```

**Contract — UNCHANGED by this feature.** The shared `feeds` prop continues to return **every** feed the user owns, including hidden ones. The dashboard Feeds-tab list (`FeedCard` mapping in `dashboard.tsx`) depends on this to keep showing all feeds. Hiding is enforced only at the picker (below), never here.

The serialized array now includes `is_hidden_from_selector` (from `feed-data-contract.md`).

## Page: `feeds/edit` (`GET /feeds/{feed}/edit` → `FeedController::edit`)

Props rendered to `feeds/edit`:

| Prop | Type | Change |
|---|---|---|
| `feed` | `Feed` (with `items.libraryItem.mediaFile` eager-loaded, ordered by feed_type) | Now includes `is_hidden_from_selector`. |
| `userLibraryItems` | `LibraryItem[]` (with `mediaFile`) | **CHANGED:** `limit(100)` removed — full personal library returned so the "Add Media" tab can search it client-side. |

**Behavior contract of the page:**

- A two-tab switch within the feed-items section:
  - **"Feed Items" tab:** existing behavior — list of `feed.items`, drag-to-reorder (static), remove, optional display-date (append), client-side title search. Unchanged.
  - **"Add Media" tab:** `userLibraryItems` minus those already in `feed.items`, rendered in a tall (`max-h-[60vh]`) scroll container, each with a `+` add button, filtered live by a `SearchInput` (title substring, debounced) — same search UX as the dashboard feeds list.
- Adding an item moves it into the form's `items` array (existing `addLibraryItem` logic); it disappears from "Add Media" and appears under "Feed Items".
- The feed form (`FeedFormFields`) gains a "Show in Add Media list" checkbox bound to the inverse of `is_hidden_from_selector`.

## Page: `dashboard` (`GET /` and `GET /library` → `Dashboard`)

**Change contract — one line:** the `MediaUploadButton` is fed only non-hidden feeds:

```ts
<MediaUploadButton
    feeds={feeds.filter((f) => !f.is_hidden_from_selector)}
    // ...
/>
```

Everything else on the dashboard (the Feeds-tab `FeedCard` list using the full `feeds` array, the Library tab, flash messages) is unchanged. `FeedSelector` itself is **not** modified — it remains a dumb component that renders whatever feeds it receives.

## Picker filtering rules (testable)

1. Hidden feeds (`is_hidden_from_selector === true`) MUST NOT appear in the `FeedSelector` checkbox list inside the Add Media dialog.
2. Non-hidden feeds MUST appear (subject to the existing "feeds list" — there is no other filter today).
3. Hidden feeds MUST still appear in the dashboard Feeds-tab list.
4. Hiding a feed MUST NOT change its RSS feed, share page, or existing `FeedItem` memberships.
5. The filter is purely client-side; no new request is made when opening the Add Media dialog.
