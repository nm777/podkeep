# Research: Video Podcast Support

**Feature**: 014-video-podcast-support  
**Date**: 2026-07-12

## R1: Video-to-Audio Conversion

**Decision**: Use ffmpeg directly via Symfony Process component.

**Command**: `ffmpeg -i input.mp4 -vn -acodec libmp3lame -q:a 0 output.mp3`

**Rationale**: ffmpeg is already available in the production Docker image (bundled with yt-dlp). The Symfony Process component is already used by YouTubeDownloader for yt-dlp commands. The `-vn` flag drops the video stream; libmp3lame produces MP3 output matching existing audio files.

**Alternatives considered**:
- Use yt-dlp for conversion → rejected (yt-dlp is for downloading, not local file conversion)
- Use PHP FFmpeg bindings (PHP-FFMpeg) → rejected (unnecessary dependency; direct CLI is simpler and already the pattern)

## R2: Video File Detection

**Decision**: Extend MediaValidator to accept video signatures and use Symfony's `File::mimeType()` for detection (already in place). Add video signatures to the allowed list.

**Video signatures**:
- MP4/M4V: `ftyp` at offset 4 (already partially handled — currently detected as `audio/mp4`)
- WebM/Matroska: `\x1A\x45\xDF\xA3` (EBML header)
- AVI: `RIFF` + `AVI ` at offset 8 (currently `RIFF` is accepted but mapped to `audio/wav`)

**MP4 audio vs video differentiation**: MP4 containers can hold audio-only or audio+video. Use Symfony's `File::mimeType()` which returns `video/mp4` for video MP4 and `audio/mp4` or `audio/m4a` for audio-only MP4. Trust the system-level detection rather than trying to parse the container ourselves.

**Rationale**: The existing `File::mimeType()` call in MediaValidator already does system-level detection. We just need to stop rejecting its video results and stop hardcoding audio MIME types for containers that could be either.

## R3: YouTube Video Download

**Decision**: Add a `downloadVideo()` method to YouTubeDownloader that omits `--extract-audio` and downloads best-quality MP4.

**Command**: `yt-dlp --format 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4' --no-playlist --output ...`

**Rationale**: YouTubeDownloader already has the pattern for running yt-dlp via Symfony Process. The `--format` flag selects the best MP4 video+audio combo. Falls back to best available if separate streams aren't available.

**Alternatives considered**:
- Always download video then extract audio if needed → rejected (wastes bandwidth for audio-only requests)
- Use a single download method with a flag → cleaner, but the audio and video commands differ enough to warrant separate methods

## R4: Media Type Field

**Decision**: Add `media_type` enum field (audio | video) to `library_items` table with default `audio`.

**Rationale**: The media type determines:
1. RSS enclosure MIME type (already handled via `mime_type` on MediaFile — video files get video MIME types automatically)
2. Which player to render in the UI (audio vs video element)
3. Whether to convert video to audio during processing

Storing the user's CHOICE (not auto-detecting) makes the behavior explicit and user-controlled. The `mime_type` on MediaFile already reflects the actual file type; `media_type` on LibraryItem reflects the user's intent.

**Migration**: Simple `ALTER TABLE` adding nullable string with default `audio`. Existing items get `audio`.

## R5: RSS Feed — No Changes Needed

**Decision**: The RSS template already uses `$item->libraryItem->mediaFile->mime_type` for the enclosure type. Video files stored with `video/mp4` MIME type will automatically produce `type="video/mp4"` in the enclosure.

**Verification**: The MediaValidator's `detectMimeType()` method already uses Symfony's `File::mimeType()` which returns correct video MIME types for video files. The MediaFile model stores whatever MIME type the validator returns. The RSS template reads from MediaFile. No template change needed.

## R6: Media Type Selector UI

**Decision**: Add a toggle/radio in the media upload form: "Audio" (default) and "Video". When the source is detected as audio-only, the selector defaults to "Audio" and "Video" is disabled. When the source is video, both options are available.

**Rationale**: The user explicitly wants to choose at creation time. The selector should be smart about what it offers — don't offer "Video" for audio-only sources, and don't offer "Audio only" if the source is already audio.

**Timing**: The selector appears BEFORE the upload/import starts (the user chooses their intent), not after (which would require re-processing). For uploads, the file type can be pre-detected from the file extension/MIME type client-side. For URL/YouTube imports, the choice is offered upfront.

## R7: Video Player

**Decision**: Extend the existing MediaPlayer component to conditionally render a `<video>` element for video items.

**Detection**: Use `libraryItem.media_type === 'video'` or check if `mime_type` starts with `video/`. Either works; `media_type` is more explicit.

**Implementation**: Add a conditional in MediaPlayer: if video, render `<video controls src={url} />` with appropriate styling; otherwise render the existing `<audio>` element. The dialog/sheet panel layout stays the same.
