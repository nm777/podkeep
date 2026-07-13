# Quick Start: Video Podcast Support

**Feature**: 014-video-podcast-support  
**Date**: 2026-07-12

## What This Feature Adds

- **Media type selector** when adding content: choose "Audio only" (extract audio from video) or "Keep video" (store as-is)
- **Video-to-audio conversion** via ffmpeg for video sources where user wants audio
- **YouTube video download** (currently YouTube only extracts audio)
- **Video player** in the app for video items (conditional `<video>` element)
- **RSS enclosures** automatically use correct video MIME types (no template change needed)

## Key Files

### New
- `src/app/Enums/MediaType.php` — audio | video enum
- `src/app/Services/MediaProcessing/VideoToAudioConverter.php` — ffmpeg wrapper
- `src/database/migrations/2026_07_12_add_media_type_to_library_items.php` — add media_type column
- `src/tests/Feature/VideoPodcastTest.php` — video processing + RSS tests

### Modified (Backend)
- `src/app/Models/LibraryItem.php` — add media_type fillable + cast
- `src/app/Services/MediaProcessing/MediaValidator.php` — accept video signatures, detect video MIME
- `src/app/Services/MediaProcessing/MediaDownloader.php` — accept video content signatures
- `src/app/Services/MediaProcessing/MediaProcessingService.php` — route to converter for audio-only from video
- `src/app/Services/YouTube/YouTubeDownloader.php` — add downloadVideo() method
- `src/app/Services/YouTube/YouTubeProcessingService.php` — accept media_type choice
- `src/app/Http/Controllers/LibraryController.php` — accept media_type in store
- `src/app/Http/Requests/StoreLibraryRequest.php` — validate media_type

### Modified (Frontend)
- `src/resources/js/components/media-player.tsx` — conditional video/audio element
- `src/resources/js/components/media-upload-button.tsx` — media type selector
- `src/resources/js/types/index.d.ts` — add media_type to LibraryItem type

### NOT Modified (already works)
- `src/resources/views/rss.blade.php` — already uses `$mediaFile->mime_type` for enclosure type
- `src/app/Http/Controllers/MediaController.php` — already uses `$mediaFile->mime_type` for Content-Type

## Migration Notes

- New `media_type` column defaults to `audio` — existing items are unaffected
- No data migration needed for existing audio files
- ffmpeg must be available in production (already present via yt-dlp)

## Testing Quick Start

```bash
php artisan test --filter=VideoPodcast
php artisan test tests/Feature/RssFeedTest.php
```
