# Feature Specification: Video Podcast Support with Media Type Selection

**Feature Branch**: `014-video-podcast-support`  
**Created**: 2026-07-12  
**Status**: Draft  
**Input**: User description: "I'd like to support video podcasts as well. When creating new media though I want to be able to choose whether to make the content a video or audio item. Some items will be videos that I want audio only."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Choose Media Type When Adding Content (Priority: P1)

When a user uploads a file or imports from a URL, they choose whether the item should be stored as **video** or **audio**. If the source is a video file and the user selects "audio," the system extracts just the audio track and discards the video. If the user selects "video," the file is kept as-is. For audio-only sources, "audio" is the default and only option. This choice is made at creation time and determines how the file is stored, served, and listed in RSS feeds.

**Why this priority**: The media type selection is the foundational choice that governs all downstream behavior — storage, RSS enclosure type, playback, and processing. Without it, nothing else in this feature can function.

**Independent Test**: Upload an MP4 video file and choose "audio." Verify the system produces an audio-only file. Upload the same file and choose "video." Verify the video file is stored as-is. Check both items in the RSS feed — the audio item has `audio/mpeg` enclosure, the video item has `video/mp4`.

**Acceptance Scenarios**:

1. **Given** a user is uploading a video file (e.g., .mp4), **When** they choose "Audio only," **Then** the system extracts the audio track and stores an audio file, discarding the video
2. **Given** a user is uploading a video file, **When** they choose "Keep video," **Then** the file is stored as-is with its original video format
3. **Given** a user is uploading an audio file (e.g., .mp3), **When** the media type selector appears, **Then** "Audio" is selected and "Video" is not offered (audio files cannot become video)
4. **Given** a user is importing from a URL that serves video content, **When** they choose "Audio only," **Then** the system downloads and extracts just the audio track
5. **Given** a media item has been created, **Then** its media type (audio or video) is visible in the library list

---

### User Story 2 - Video Files in RSS Feeds (Priority: P2)

Items marked as video appear in RSS feeds with the correct video MIME type in the `<enclosure>` tag (e.g., `video/mp4`). Items marked as audio continue to use audio MIME types (e.g., `audio/mpeg`). Podcast apps that support video (like Apple Podcasts) display and play video episodes with the video player. Audio-only apps receive the audio enclosure as normal.

**Why this priority**: The RSS enclosure type is what makes a podcast a "video podcast" vs an "audio podcast." Without correct MIME types, video items won't be recognized or played by podcast apps.

**Independent Test**: Add a video item to a feed. Fetch the RSS XML. Verify the enclosure has `type="video/mp4"` (or appropriate video MIME). Add an audio item to the same feed. Verify it has `type="audio/mpeg"`. A feed can contain both audio and video items.

**Acceptance Scenarios**:

1. **Given** a feed contains a video item, **When** the RSS feed is generated, **Then** the enclosure uses the correct video MIME type (e.g., `video/mp4`)
2. **Given** a feed contains both audio and video items, **When** the RSS feed is generated, **Then** each item has the correct MIME type matching its media type
3. **Given** a video item in a feed, **When** a podcast app subscribes, **Then** the app recognizes the episode as video content
4. **Given** a feed has mixed audio and video content, **Then** both types are listed in the RSS feed without errors

---

### User Story 3 - Video Playback in the App (Priority: P3)

When a user clicks play on a video item in the library, a video player appears (instead of the audio player). The video player shows the video frame and standard playback controls. Audio items continue to use the existing audio player. The player type is determined by the item's media type.

**Why this priority**: Users need to preview and play their video content within PodKeep, not just serve it to external apps. This provides a complete experience but is secondary to the RSS and storage concerns.

**Independent Test**: Add a video item. Click play in the library. Verify a video player appears with video visible. Click play on an audio item. Verify the audio player appears as before.

**Acceptance Scenarios**:

1. **Given** a video item in the library, **When** the user clicks play, **Then** a video player is shown with video frames and playback controls
2. **Given** an audio item in the library, **When** the user clicks play, **Then** the existing audio player is shown (no change from current behavior)
3. **Given** a video is playing, **Then** the user can pause, seek, and adjust volume using standard video controls

---

### Edge Cases

- What happens if a user uploads a file with an ambiguous format (e.g., .webm that could be audio or video)? The system auto-detects the actual media type from the file content and offers the appropriate choices.
- What happens if ffmpeg is not available for video-to-audio conversion? The system shows an error explaining that audio extraction is not available.
- What happens with very large video files (1GB+)? The existing streaming download fix handles large files; the media server uses range requests for playback. Processing time will be longer.
- Can a user change the media type after creation (e.g., convert a stored video to audio later)? Not in this version — the choice is made at creation time. Future versions could add conversion.
- What about YouTube imports? YouTube already extracts audio via yt-dlp. Users can choose "video" to keep the video track instead of audio-only extraction.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a media type selector (Audio / Video) when adding new content via upload, URL import, or YouTube
- **FR-002**: For video sources where the user selects "Audio only," System MUST extract the audio track using available tools (e.g., ffmpeg) and store only the audio file
- **FR-003**: For video sources where the user selects "Keep video," System MUST store the original video file as-is
- **FR-004**: For audio-only sources, System MUST default to "Audio" and not offer a "Video" option
- **FR-005**: System MUST auto-detect whether an uploaded or imported file contains video or audio-only content
- **FR-006**: Each media item MUST have a media type attribute (audio or video) that determines its enclosure MIME type in RSS feeds
- **FR-007**: RSS feed enclosure tags MUST use the correct MIME type based on the item's media type (e.g., `video/mp4` for video, `audio/mpeg` for audio)
- **FR-008**: The media player MUST render a video player for video items and the existing audio player for audio items
- **FR-009**: The library list MUST indicate whether each item is audio or video (e.g., an icon or badge)
- **FR-010**: A feed MAY contain a mix of audio and video items — the RSS feed MUST handle both correctly

### Key Entities *(include if feature involves data)*

- **LibraryItem**: Gains a `media_type` attribute (`audio` or `video`) set at creation time. Determines storage format, RSS enclosure type, and player type.
- **MediaFile**: Stores both audio and video files. The `mime_type` field already exists and reflects the actual file type. Video files use video MIME types (e.g., `video/mp4`, `video/webm`).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can create a video podcast item by uploading a video file and selecting "Keep video" — the item appears in RSS feeds with a video enclosure type
- **SC-002**: Users can extract audio from a video source by selecting "Audio only" — the resulting item is a standard audio podcast episode indistinguishable from a directly uploaded audio file
- **SC-003**: A single feed containing both audio and video items produces valid RSS with correct MIME types for each item
- **SC-004**: Video items play in the app with a visible video player; audio items continue using the audio player with zero regressions
- **SC-005**: The media type selector appears for 100% of new content creation flows (upload, URL, YouTube) when the source contains video

## Assumptions

- **ffmpeg availability**: The system assumes ffmpeg is available for video-to-audio extraction. The production Docker image already includes yt-dlp (which bundles ffmpeg). If ffmpeg is not available, audio extraction fails gracefully with an error message.
- **Supported video formats**: MP4 (H.264/AAC) is the primary target format since it's the most widely supported by podcast apps. WebM and MKV are accepted but may not play in all podcast apps.
- **Storage**: Video files are stored in the same `storage/app/public/media/` directory as audio files, using the same hash-based naming convention. Video files will be larger but the storage system already handles large files.
- **Media type is final**: The audio/video choice is made at creation time and cannot be changed later (except by re-adding the content). This keeps the feature scope manageable.
- **YouTube video support**: YouTube imports currently extract audio via `yt-dlp --extract-audio`. For video, the system would use `yt-dlp` without the `--extract-audio` flag to download the video file. This requires more storage and bandwidth.
- **Existing audio items**: All existing items default to `media_type: audio`. No migration of content is needed — just adding the field with a default value.
- **File validation**: The current audio signature validation (checking for MP3/WAV/OGG headers) is extended to also accept video signatures (MP4/WebM/MKV headers).
