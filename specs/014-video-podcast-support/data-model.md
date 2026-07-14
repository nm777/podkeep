# Data Model: Video Podcast Support

**Feature**: 014-video-podcast-support  
**Date**: 2026-07-12

## Entity Changes

### LibraryItem (modified)

| Field | Type | Change | Description |
|-------|------|--------|-------------|
| `media_type` | enum(string) | **Added** | `audio` or `video`. Default: `audio`. Set at creation time based on user choice. |

**Enum: `MediaType`**:
- `Audio = 'audio'` — Audio-only content (existing behavior)
- `Video = 'video'` — Video content (kept as-is or from video source)

### MediaFile (unchanged)

The `mime_type` field already stores the actual file MIME type. Video files stored with `video/mp4` (or similar) work correctly in the existing RSS template and MediaController — no changes needed.

### New: VideoToAudioConverter (service)

Not a database entity — a service class that wraps ffmpeg for extracting audio from video files.

**Input**: path to a video file  
**Output**: path to a converted MP3 audio file  
**Command**: `ffmpeg -i {input} -vn -acodec libmp3lame -q:a 0 {output}`

## Processing Flow

### Upload/Import with Media Type Choice

```
User selects source + media_type
         │
    ┌────┴────┐
    │ Source? │
    └────┬────┘
         │
    ═════╪═════════════════════════════════
    URL/Upload              YouTube
         │                      │
    Download to temp        yt-dlp download
         │              ┌───────┴───────┐
         │          audio_only      video
         │              │               │
         │         --extract-audio   --format mp4
         │              │               │
    ═════╪══════════════╪═══════════════╪═══
         │              │               │
    Is video source & user wants audio?
         │         │           │
        YES        NO          NO
         │         │           │
    ffmpeg -vn     │           │
    extract audio  │           │
         │         │           │
    ═════╪═════════╪═══════════╪═══
         │         │           │
    Validate → Hash → Store → Create MediaFile
```

## RSS Enclosure (no change)

```
MediaFile.mime_type = "video/mp4"
       ↓
RSS template: type="{{ $item->libraryItem->mediaFile->mime_type }}"
       ↓
<enclosure ... type="video/mp4" />
```

Works automatically — no template change needed.
