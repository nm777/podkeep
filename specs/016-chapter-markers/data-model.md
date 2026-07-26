# Data Model: Media Chapter Markers

**Branch**: `016-chapter-markers` | **Date**: 2026-07-24

One new entity (`Chapter`) attached to the existing `MediaFile`. No changes to `LibraryItem`, `Feed`, or `FeedItem` structures.

## Entities

### MediaFile (existing, modified)

The audio/video content record; already holds `duration`, `file_hash` (dedup), `filesize`, `mime_type`. Multiple `LibraryItem`s can share one `MediaFile`.

**New relationship:**

| Relationship | Type | Notes |
|---|---|---|
| `chapters` | hasMany `Chapter` | `->orderBy('start_time', 'asc')`. A media file has 0–20 chapters. Deleting a media file cascades to its chapters. |

Because chapters live here, **all library items sharing a media file share its chapters** (same audio → same chapters). This is intentional (research.md R1).

**New generation-state columns (nullable; used only by Story 2's automatic proposal):**

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `transcript` | json | YES | whisper.cpp segment output (text + start/end). Persisted so re-proposals skip re-transcription. |
| `chapter_generation_status` | enum(`pending`,`processing`,`completed`,`failed`) | YES | Drives UI progress/polling + retry. |
| `chapter_proposal` | json | YES | Proposed `{start_time,title}[]` held for review; **never** read by RSS. |
| `chapter_generation_error` | text | YES | Failure detail surfaced to the user. |

These hold AI-proposed drafts only; nothing in them is published until the user saves, which writes the real `chapters` rows. A re-proposal overwrites `chapter_proposal` (and reuses `transcript` if present).

### Chapter (new)

A named, timestamped segment of a single media file.

| Column | Type | Nullable | Default | Notes |
|---|---|---|---|---|
| `id` | bigint (PK) | NO | auto | |
| `media_file_id` | bigint (FK → media_files) | NO | — | cascade on delete |
| `start_time` | integer | NO | — | seconds offset from media start; `≥ 0` and `< media_file.duration` |
| `title` | string(255) | NO | — | non-empty |
| `created_at` / `updated_at` | timestamps | NO | — | |

**Constraints & indexes:**
- Unique composite `(media_file_id, start_time)` — no duplicate start times per file.
- Index on `media_file_id` for efficient lookup by file.
- **Max 20 rows per `media_file_id`** — enforced in application validation (`ChapterSyncRequest`: `chapters` array `max:20`), not via a DB constraint (a per-parent row-count cap has no native SQL constraint; app enforcement + a guard in the controller before insert).

**Relationships:**
- `Chapter belongsTo MediaFile`.
- No direct relationship to `LibraryItem`/`Feed`/`FeedItem` — chapters surface in the RSS feed via the media file that an episode references.

**Validation rules (from FR-002/003/004/010):**
- Whole sync payload `chapters` array: `array`, `max:20`.
- Each `chapters.*.title`: `required`, `string`, `min:1`, `max:255`.
- Each `chapters.*.start_time`: `required`, `integer`, `min:0`, and `< media_file.duration` (duration resolved server-side).
- No two entries in a payload may share a `start_time` (unique within the array; backed by the DB unique index).

**State transitions:** None meaningful. Chapters are replaced wholesale on each sync (replacement semantics like feed items). A media file moves between "0 chapters" and "1–20 chapters" only via full-array sync.

**Authorization:** Mutating chapters requires owning the parent media file (`media_file.user_id === Auth::id`), enforced by `ChapterPolicy`/Gate. Reads in the RSS feed are governed by the feed's existing public/token access rules (unchanged).

### LibraryItem / Feed / FeedItem (unchanged structurally)

`LibraryItem` gains no columns. The chapter editor is reached through a library item (`$libraryItem->mediaFile->chapters`). RSS rendering iterates feed items → library item → media file → chapters.

## Migration

A single additive migration: `2026_07_24_HHMMSS_create_chapters_table.php`

```php
Schema::create('chapters', function (Blueprint $table) {
    $table->id();
    $table->foreignId('media_file_id')->constrained()->cascadeOnDelete();
    $table->integer('start_time');
    $table->string('title', 255);
    $table->timestamps();

    $table->unique(['media_file_id', 'start_time']);
    $table->index('media_file_id');
});
```

- Additive only → no data loss, no downtime.
- No backfill needed (existing media files simply have zero chapters).

**Second migration — `media_files` generation state:**

```php
Schema::table('media_files', function (Blueprint $table) {
    $table->json('transcript')->nullable()->after('duration');
    $table->string('chapter_generation_status', 16)->nullable()->after('transcript');
    $table->json('chapter_proposal')->nullable()->after('chapter_generation_status');
    $table->text('chapter_generation_error')->nullable()->after('chapter_proposal');
});
```

Also additive/nullable → no backfill. (Enum stored as `string(16)` for SQLite-test portability; constrained in validation.)

## TypeScript Types

`resources/js/types/index.d.ts` — new `Chapter` type; `MediaFile` gains optional `chapters`:

```ts
export interface Chapter {
    id: number;
    media_file_id: number;
    start_time: number;   // seconds
    title: string;
    created_at: string;
    updated_at: string;
}

export interface MediaFile {
    // ...existing fields...
    duration?: number;
    chapters?: Chapter[];   // NEW — populated on the edit view / player
    transcript?: { start: number; end: number; text: string }[] | null;  // NEW (Story 2)
    chapter_generation_status?: 'pending' | 'processing' | 'completed' | 'failed' | null;  // NEW
    chapter_proposal?: { start_time: number; title: string }[] | null;  // NEW — drafts, not published
    chapter_generation_error?: string | null;  // NEW
}
```

The **proposal** is driven server-side: a `POST /library/{library_item}/chapters/generate` kicks off transcription + LLM segmentation jobs on the dedicated `chapters` queue (see [chapter-generation-pipeline.md](chapter-generation-pipeline.md)); there is no client-side `proposeChapters` helper in this revised design.
