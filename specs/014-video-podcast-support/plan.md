# Implementation Plan: Video Podcast Support

**Branch**: `014-video-podcast-support` | **Date**: 2026-07-12 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/014-video-podcast-support/spec.md`

## Summary

Add video podcast support: users choose "Audio only" or "Keep video" when adding content. Video sources can be converted to audio via ffmpeg. Video items get correct MIME types in RSS enclosures. The media player renders a `<video>` element for video items. Existing audio behavior is unchanged.

## Technical Context

**Language/Version**: PHP 8.4 (Laravel 13), TypeScript (React 19+)  
**Primary Dependencies**: Laravel Framework 13, Inertia.js v3, Tailwind CSS v4, Pest PHP v4, yt-dlp + ffmpeg (in production image)  
**Storage**: PostgreSQL (production), SQLite (tests), local public disk for media files  
**Testing**: Pest PHP v4 (backend), feature tests required  
**Target Platform**: Web application with Docker containerization  
**Performance Goals**: Video processing < 10min (constitution limit), RSS generation < 5s  
**Constraints**: ffmpeg available via yt-dlp in production; streaming download already fixed; range request serving already implemented  

## Constitution Check

- [x] **API-First**: Backend media type field, processing pipeline, RSS changes before frontend player
- [x] **Media Processing**: Async jobs for video download/conversion (existing queue infrastructure)
- [x] **Test-Driven**: Feature tests for media type selection, video validation, RSS enclosure types
- [x] **Feed Standards**: RSS enclosure uses correct MIME type per item (video/mp4 for video)
- [x] **Security**: File validation extended for video; existing auth/authorization preserved
- [x] **Performance**: ffmpeg processing within 10min limit; large files use streaming download

## Project Structure

```text
src/
├── app/
│   ├── Enums/
│   │   └── MediaType.php                    # NEW: audio | video enum
│   ├── Http/Controllers/
│   │   ├── LibraryController.php            # Modified: accept media_type in creation flow
│   │   └── MediaController.php              # No changes (already uses mime_type)
│   ├── Http/Requests/
│   │   └── StoreLibraryRequest.php          # Modified: validate media_type
│   ├── Models/
│   │   └── LibraryItem.php                  # Modified: add media_type field + cast
│   ├── Services/
│   │   └── MediaProcessing/
│   │       ├── MediaDownloader.php          # Modified: accept video signatures
│   │       ├── MediaValidator.php           # Modified: validate video files, detect media type
│   │       ├── VideoToAudioConverter.php    # NEW: ffmpeg wrapper for audio extraction
│   │       └── MediaProcessingService.php   # Modified: route to converter when audio-only requested
│   └── Services/YouTube/
│       ├── YouTubeDownloader.php            # Modified: add downloadVideo() method
│       └── YouTubeProcessingService.php     # Modified: accept media_type choice
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── media-player.tsx             # Modified: conditional video/audio element
│   │   │   └── media-upload-button.tsx      # Modified: media type selector
│   │   └── types/index.d.ts                 # Modified: media_type on LibraryItem
│   └── views/
│       └── rss.blade.php                    # No changes needed (already uses mime_type)
├── database/
│   └── migrations/
│       └── 2026_07_12_add_media_type_to_library_items.php  # NEW
└── tests/
    └── Feature/
        └── VideoPodcastTest.php             # NEW: video processing + RSS enclosure tests
```
