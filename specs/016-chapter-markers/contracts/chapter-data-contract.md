# Contract: Chapter Data & Sync Payload

**Branch**: `016-chapter-markers` | **Date**: 2026-07-24

Laravel + Inertia app. This file covers the `Chapter` entity, the server-side validation contract, and the client→server sync payload. RSS rendering is in [rss-chapter-format.md](rss-chapter-format.md); the editor flow is in [chapter-editor-payload.md](chapter-editor-payload.md).

## Chapter entity (server shape)

`chapters` table:

| Column | Type | Default | Source of truth |
|---|---|---|---|
| `id` | bigint PK | auto | |
| `media_file_id` | bigint FK→media_files | — | parent media file (cascade on delete) |
| `start_time` | integer | — | seconds offset from media start; `≥ 0` and `< duration` |
| `title` | string(255) | — | non-empty |
| `created_at`, `updated_at` | timestamps | — | |

Constraints: `unique(media_file_id, start_time)`; `index(media_file_id)`; **≤ 20 rows per `media_file_id`** (app-enforced).

## Chapter sync payload (client → server)

**Route (new, web/Inertia):** `PUT /library/{library_item}/chapters` → `ChapterController@sync`, behind `auth`/`verified`/`approved`.

**Semantics:** Full-replacement sync — the submitted array **is** the media file's complete chapter set (mirrors `FeedController::syncFeedItems`). Existing chapters not in the payload are deleted; submitted ones are upserted.

**Payload:**

| Field | Type | Required | Validation |
|---|---|---|---|
| `chapters` | array | no (omit/empty = clear all) | `array`, `max:20` |
| `chapters.*.start_time` | int | yes (per item) | `required, integer, min:0`, and **`< media_file.duration`** (duration resolved server-side from the route-bound library item's media file) |
| `chapters.*.title` | string | yes (per item) | `required, string, min:1, max:255` |

**Additional rules enforced in `ChapterSyncRequest`:**
- No two entries in `chapters` share the same `start_time`.
- The bound library item's media file must exist and be processed (`duration` non-null); otherwise chapters cannot be validated/synced.

**Response:** Redirect back to the library view with a success flash (Inertia convention), matching `LibraryController` responses.

**Authorization:** `ChapterPolicy` (or Gate) verifies `Auth::id() === $libraryItem->mediaFile->user_id` before any side effect. Non-owners get 403.

**Side effects on success:** Replace chapters for the media file; clear `rss.{feed_id}` cache for every feed containing any library item that uses this media file (see [rss-chapter-format.md](rss-chapter-format.md)).

## TypeScript `Chapter` type (client shape)

`resources/js/types/index.d.ts`:

```ts
export interface Chapter {
    id: number;
    media_file_id: number;
    start_time: number;   // seconds
    title: string;
    created_at: string;
    updated_at: string;
}
```

`MediaFile` gains optional `chapters?: Chapter[]` (populated only where needed: edit view, player).

## Chapter generation (server-side async — see chapter-generation-pipeline.md)

Content-aware proposals are produced **server-side**: `POST /library/{library_item}/chapters/generate` dispatches transcription + LLM topic-segmentation jobs on the dedicated `chapters` queue, writing the result to `media_file.chapter_proposal` (drafts, not published). The editor polls status and loads that proposal for review. There is **no client-side proposal helper** in this design. Details: [chapter-generation-pipeline.md](chapter-generation-pipeline.md).
