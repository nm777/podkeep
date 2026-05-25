# Quickstart: Podcast Share Player Page

## What This Feature Adds

A public web page where podcast recipients can browse episodes and listen in the browser without a podcast app. Feed owners get a "Copy Share Link" button on their feed cards.

## Files to Create

### Backend

1. **`src/app/Http/Controllers/ShareController.php`**
   - `show(Request, $user_guid, $feed_slug)` method
   - Finds feed by `user_guid` + `slug` with eager-loaded items
   - Access check: `is_public` or valid `?token=`
   - Filters to completed items with media files
   - Returns `Inertia::render('share/show', [...props])`

### Frontend

2. **`src/resources/js/pages/share/show.tsx`**
   - Standalone page (no app sidebar/header)
   - Displays feed title, description, cover image
   - Lists episodes with play buttons
   - Inline audio player
   - "Copy RSS URL" button
   - Dark mode support

3. **`src/resources/js/components/share-player.tsx`**
   - Reusable audio player component for the share page
   - Uses native `<audio>` element with controls
   - Handles media URL construction

4. **`src/resources/js/components/share-episode-list.tsx`**
   - List of episodes with title, description, date, duration
   - Click to play in the share player

### Routes

5. **`src/routes/web.php`** (modify)
   - Add: `Route::get('share/{user_guid}/{feed_slug}', [ShareController::class, 'show'])->name('share.show')->middleware('throttle:120,1');`
   - Place near the existing RSS route (line 18 area)

### Utility

6. **`src/resources/js/lib/subscribe-urls.ts`** (modify)
   - Add `getShareUrl(feed: Feed): string` helper

### Existing Components

7. **`src/resources/js/components/feed-card.tsx`** (modify)
   - Add "Copy Share Link" button with `Share` icon from lucide-react

### Types

8. **`src/resources/js/types/index.d.ts`** (modify)
   - Add `ShareEpisode` and `ShareFeed` types

## Files to Test

9. **`src/tests/Feature/ShareControllerTest.php`**
   - Test: public feed accessible without token
   - Test: private feed accessible with valid token
   - Test: private feed returns 404 without token
   - Test: private feed returns 404 with wrong token
   - Test: non-existent feed returns 404
   - Test: only completed items with media shown
   - Test: episodes ordered by sequence

## Key Patterns to Follow

- **Access control**: Copy `RssController::show()` pattern exactly (lines 14-26)
- **Eager loading**: `Feed::with(['items.libraryItem.mediaFile'])` as in RssController
- **Media URLs**: Use existing `/files/{path}` route with `?feed_token=` for private feeds
- **Inertia rendering**: `Inertia::render('share/show', $props)` as used throughout the app
- **Component styling**: Follow existing Tailwind patterns from `media-player.tsx` and `feed-card.tsx`
