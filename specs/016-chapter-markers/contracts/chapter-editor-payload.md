# Contract: Chapter Editor Flow & Payload

**Branch**: `016-chapter-markers` | **Date**: 2026-07-24

How the user reaches and uses the chapter editor in the UI, and the exact payload it sends.

## Entry point

The chapter editor is a section inside the existing **"Edit Media" sheet** on the dashboard Library tab (`src/resources/js/pages/dashboard.tsx`), rendered by a new `src/resources/js/components/chapter-editor.tsx`.

- Shown **only when `libraryItem.media_file?.duration` is present** (media processed). When duration is absent, the section is hidden — chapters can't be authored without a duration.
- Reached from a library item's edit action — this is both "when the file is added" (right after processing completes, the item is editable) and "after the fact" (anytime later). The add-media sheet itself does not offer chapters (no duration yet).

## Editor data source

The library item's media file must carry its chapters into the edit view. The dashboard data query (the `libraryItems` closure in `routes/web.php`) eager-loads `mediaFile`; it must also eager-load `mediaFile.chapters` so the editor opens with existing chapters without an extra request.

## Editor behavior (chapter-editor.tsx)

- Maintains a local list of `{ start_time, title }` rows (seeded from `mediaFile.chapters`, or empty).
- **Add row:** appends a blank chapter (default `start_time` = previous end or 0, blank title). Hard cap: once 20 rows exist, "Add" is disabled with a message ("Up to 20 chapters").
- **Generate from content (Story 2):** a "Generate chapters from content" button `POST`s to `/library/{library_item}/chapters/generate`, then the editor **polls** `media_file.chapter_generation_status` (reuse the dashboard's ~5s reload pattern) showing progress. On `completed` it loads `media_file.chapter_proposal` into the list as editable drafts; on `failed` it shows `chapter_generation_error` with a retry. Re-generation overwrites the proposal (never touches already-saved chapters until the user saves again). Disabled while a generation is in progress.
- **Remove row:** deletes a chapter from the local list.
- **Re-time / rename:** inline editing of `start_time` (seconds, or an `MM:SS`/`HH:MM:SS` input) and `title`.
- **Save:** posts the full array via the sync contract (see [chapter-data-contract.md](chapter-data-contract.md)).
- Display order is by `start_time` ascending (sorted on save).
- Empty/invalid rows are caught either client-side (advisory) or server-side (authoritative).

## Payload on save

`PUT /library/{library_item}/chapters` (Inertia form post), body:

```json
{
  "chapters": [
    { "start_time": 0,     "title": "Intro" },
    { "start_time": 330,   "title": "Guest interview" },
    { "start_time": 4365,  "title": "Q&A" }
  ]
}
```

- Full-replacement semantics (the array IS the new complete set).
- Omitting `chapters` or sending `[]` clears all chapters.
- Validation, authorization, and RSS cache invalidation are per [chapter-data-contract.md](chapter-data-contract.md) and [rss-chapter-format.md](rss-chapter-format.md).

## P3 — In-app player (separate, deferrable)

`media-player.tsx` shows the chapter list (from `media_file.chapters`) and seeks the `<audio>`/`<video>` element to `start_time` on click. Requires adding a ref to the media element. Shares no new payload — it reads chapters already attached to the library item's media file on the share/dashboard pages.
