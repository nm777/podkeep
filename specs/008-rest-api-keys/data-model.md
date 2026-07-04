# Phase 1: Data Model — REST API with API Key Authentication

**Feature**: 008-rest-api-keys
**Date**: 2026-07-03

## New Entity: Personal Access Token (API Key)

Managed by Laravel Sanctum v4. Created via `php artisan install:api`.

### Migration: `personal_access_tokens`

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigint (PK, unsigned) | No | Auto-increment |
| `tokenable_type` | string(255) | No | Morph class — always `App\Models\User` |
| `tokenable_id` | bigint (unsigned) | No | Morph id — references `users.id` |
| `name` | string(255) | No | User-supplied label (e.g., "CI/CD uploads") |
| `token` | string(64) | No | SHA-256 hash of plaintext secret (unique index) |
| `abilities` | text | Yes | JSON array of scope strings, default `["*"]` |
| `last_used_at` | timestamp | Yes | Updated each time the key authenticates a request |
| `expires_at` | timestamp | Yes | Optional per-token expiration |
| `created_at` | timestamp | Yes | Standard timestamp |
| `updated_at` | timestamp | Yes | Standard timestamp |

**Indexes**: `token` (unique), `tokenable_type, tokenable_id` (composite).

**Relationships**:
- Belongs to `User` via polymorphic `tokenable` (Sanctum's `HasApiTokens` trait provides `$user->tokens()` hasMany).

**Validation rules** (for key creation):
- `name`: required, string, max:255

**Security properties**:
- Plaintext token format: `{id}|{40-char-random}` — shown to user exactly once at creation.
- Stored `token` column is `hash('sha256', $plaintext)` — never decryptable.
- Lookup: extract `id` from plaintext prefix, find row by id, verify hash with `hash_equals()` (timing-safe).

### Model Changes

**`App\Models\User`** — add Sanctum trait:
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // ... existing code unchanged
}
```
This adds: `tokens()` relationship, `createToken($name, $abilities, $expiresAt)`, `currentAccessToken()`, `tokensWithAccessToken()`.

### Factory: `ApiKeyFactory` / `PersonalAccessTokenFactory`

Not needed — Sanctum tokens are created via `$user->createToken()` in tests, which handles the hashing internally. For tests that need a raw token record, use `$user->createToken('test')->plainTextToken` then authenticate via header.

---

## Existing Entities — API Shape

These entities already exist. This section documents their API resource shape (what fields the API exposes).

### Feed

**Source**: `App\Models\Feed` (existing)
**API Resource**: `App\Http\Resources\FeedResource` (existing, currently unused)

**API fields**:

| Field | Type | Notes |
|-------|------|-------|
| `id` | int | Resource identifier |
| `title` | string | User-defined name |
| `description` | string\|null | |
| `website_url` | string\|null | |
| `is_public` | boolean | Visibility flag |
| `slug` | string | URL-friendly identifier (auto-generated, unique per user) |
| `user_guid` | string (UUID) | Public identifier for RSS/share URLs |
| `token` | string\|null | Owner-gated — only exposed when `request()->user()` owns the feed |
| `items_count` | int\|null | Only present when count is eager-loaded |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Validation rules** (API create):
- `title`: required, string, max:255
- `description`: nullable, string, max:1000
- `website_url`: nullable, string, url, max:255
- `is_public`: boolean

**Auto-generated on create** (by controller, not user input):
- `slug`: `Str::slug($title)` with uniqueness suffix per user
- `user_guid`: `Str::uuid()`
- `token`: `Str::random(64)`

**Authorization**: `FeedPolicy` — `update`, `delete` check `$user->id === $feed->user_id`.

---

### Library Item

**Source**: `App\Models\LibraryItem` (existing)
**API Resource**: `App\Http\Resources\LibraryItemResource` (existing, currently unused)

**API fields**:

| Field | Type | Notes |
|-------|------|-------|
| `id` | int | Resource identifier |
| `title` | string | |
| `description` | string\|null | |
| `source_type` | string | Enum: `upload`, `url`, `youtube` |
| `source_url` | string\|null | |
| `published_at` | date\|null | |
| `is_duplicate` | boolean | |
| `processing_status` | string | Enum: `pending`, `processing`, `completed`, `failed` |
| `processing_status_display` | string | Human-readable status text |
| `is_processing` | boolean | Convenience: status is `processing` |
| `is_pending` | boolean | Convenience: status is `pending` |
| `has_completed` | boolean | Convenience: status is `completed` |
| `has_failed` | boolean | Convenience: status is `failed` |
| `processing_error` | string\|null | Present when failed |
| `media_file` | object\|null | Nested `MediaFileResource` when loaded |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Validation rules** (API create — upload):
- `title`: required, string, max:255
- `description`: nullable, string, max:1000
- `file`: required (multipart), file, mimes:mp3,mp4,m4a,wav,ogg, max:512000 (KB, ~500MB)
- `feed_ids`: nullable, array of integers (must exist in `feeds` for this user)
- `published_at`: nullable, date

**Validation rules** (API create — URL):
- `title`: required, string, max:255
- `url` or `source_url`: required, url, max:2048, must end with media extension (for `url`) or any URL (for `source_url`/YouTube)

**State transitions** (processing_status):
```
pending → processing → completed
                    └→ failed → (retry) → pending → processing → completed/failed
```

**Authorization**: `LibraryItemPolicy` — `update`, `delete`, `retry` check ownership.

---

### Media File

**Source**: `App\Models\MediaFile` (existing)
**API Resource**: `App\Http\Resources\MediaFileResource` (existing, currently unused)

**API fields**:

| Field | Type | Notes |
|-------|------|-------|
| `id` | int | |
| `public_url` | string | Accessor-generated URL for playback/serving |
| `file_hash` | string | SHA-256 of file content |
| `mime_type` | string | |
| `filesize` | int | Bytes |
| `duration` | int\|null | Seconds (currently always null — see note) |
| `source_url` | string\|null | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Note**: `duration` is never populated by current processing pipeline. Out of scope for this feature.

---

### Feed Item (Pivot)

**Source**: `App\Models\FeedItem` (existing pivot model)
**API Resource**: `App\Http\Resources\FeedItemResource` (existing, currently unused)

**API fields**:

| Field | Type | Notes |
|-------|------|-------|
| `id` | int | Pivot record id |
| `feed_id` | int | Parent feed |
| `library_item_id` | int | Attached library item |
| `sequence` | int | Ordering within feed (0-based) |
| `library_item` | object\|null | Nested `LibraryItemResource` when loaded |

**Validation rules** (attach item to feed):
- `library_item_id`: required, integer, must exist and belong to the authenticated user
- `sequence`: nullable, integer, min:0

**Authorization**: New `FeedItemPolicy` — `attach`/`detach` checks that the user owns both the feed and the library item.

---

## Entity Relationship Diagram (API-relevant)

```
User
 ├─hasMany─→ Feed
 │            └─hasMany─→ FeedItem (pivot: sequence)
 │                          └─belongsTo─→ LibraryItem
 │                                          └─belongsTo─→ MediaFile
 ├─hasMany─→ LibraryItem
 └─hasMany─→ PersonalAccessToken (Sanctum)
```

## Data Integrity Notes

- **No schema changes to existing tables** — this feature only adds the `personal_access_tokens` table (via Sanctum) and new API endpoints. Existing migrations, models, and relationships are unchanged.
- **Cascade behavior**: Sanctum's migration uses morph relations without FK constraints by default. Token deletion does not cascade to users. If a user is deleted, their tokens should be cleaned up (Sanctum provides `$user->tokens()->delete()` — add to user deletion flow if not already handled).
- **Concurrency**: Feed item reordering uses a `sequence` integer. Concurrent reorders may produce duplicate sequences. The reorder endpoint will use a transaction and re-sequence all items to handle this safely.
