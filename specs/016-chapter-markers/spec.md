# Feature Specification: Media Chapter Markers

**Feature Branch**: `016-chapter-markers`  
**Created**: 2026-07-24  
**Status**: Draft  
**Input**: User description: "Some media items could benefit from chapter markers. Let's offer the option to add chapter markers when the file is added and also after the fact if the file is long and the user decides they want to add them later. We need to keep the total number of chapters to a reasonable number (never more than 20), so for very long content the chapters may be longer."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Author & Publish Chapters (Priority: P1)

A user adds a media item (audio or video) that would benefit from chapters — for example, a long interview or a compiled episode. Once the file is processed and its length is known, the user can open the item and add up to 20 chapters, each with a start time and a title (e.g., "Intro", "Guest interview", "Q&A"). They can add, rename, re-time, and remove chapters, and the chapters appear in the published podcast feed so subscribers see and can jump between them in their podcast app of choice. Chapters are optional and can be added right when the item is added or any time later.

**Why this priority**: This delivers the core value — listeners can navigate the content via chapters in their podcast app. Without authoring and feed publication, no other chapter capability matters. It is fully usable on its own (manual authoring covers any file).

**Independent Test**: Can be tested by adding a processed media item, creating several chapters with valid start times and titles, confirming the 20-chapter limit is enforced, and confirming the chapters are present in the item's published feed — without using automatic generation or any in-app player.

**Acceptance Scenarios**:

1. **Given** a processed media item with a known duration, **When** the user opens its chapter editor and adds a chapter with a start time and title, **Then** the chapter is saved and shown in chronological order.
2. **Given** a media item that already has 20 chapters, **When** the user attempts to add another, **Then** the addition is prevented and the user is told the 20-chapter maximum has been reached.
3. **Given** a chapter whose start time is at or beyond the media's duration, **When** the user tries to save it, **Then** it is rejected with a clear message.
4. **Given** a media item has chapters, **When** its published podcast feed is viewed, **Then** the chapters (start times and titles) are included so subscribing podcast apps can display them.
5. **Given** a media item with chapters is removed from a feed or the chapters are deleted, **When** the feed is next published, **Then** the chapters no longer appear in that feed.
6. **Given** a media item is added and processing completes, **When** the user views the item, **Then** they are offered the option to add chapters at that moment (and may also return to do it later).

---

### User Story 2 - Content-Aware Chapter Proposal (Priority: P2)

For long content — especially sermons — manually choosing chapter boundaries is tedious, and even time-splits don't match the actual flow of the message. The user can request an automatic proposal, and the system **transcribes the audio and performs topic segmentation on the transcript** to propose chapters aligned to the real content (e.g., scripture reading, opening prayer, main points, application, benediction). The proposal is generated in the background and surfaced as editable drafts — never published until the user reviews and saves them. The proposal never exceeds 20 chapters; for very long content that simply means longer chapters rather than more than 20. The user renames, re-times, adds, or removes any proposed chapter before saving. This realizes the requirement that "for very long content the chapters may be longer."

**Why this priority**: A high-value convenience that makes chapters practical for long-form spoken content; it builds on the authoring capability from Story 1 and is not required for chapters to function.

**Independent Test**: Can be tested by taking a long processed media item, requesting a proposal, waiting for the background transcription + segmentation to complete, and confirming the proposed chapters are content-aligned, number no more than 20, and are editable before saving — without touching feed publication or in-app playback.

**Acceptance Scenarios**:

1. **Given** a processed media item with a known duration, **When** the user requests a chapter proposal, **Then** the system transcribes the audio and analyzes the transcript to propose content-aligned chapters, and shows progress while it works.
2. **Given** a proposal has finished, **When** the user views it, **Then** they see a set of proposed chapters (start times and topical titles) that reflect the actual content — never more than 20.
3. **Given** a proposed set of chapters, **When** the user renames, re-times, adds, or removes entries and saves, **Then** only the user's final edited set is stored and published (subject to the same 20-chapter cap and duration validation as Story 1). Proposed chapters are **not** published until the user saves.
4. **Given** a proposal is running or has run, **When** transcription/segmentation fails, **Then** the user is informed and can retry, and no partial/invalid chapters are published.
5. **Given** the heavy generation work, **When** it runs, **Then** it does not slow or block other operations (it runs isolated in the background at low priority).

---

### User Story 3 - Chapters in PodKeep's Own Players (Priority: P3)

So that the user (and visitors to a public share page) can navigate chapters inside PodKeep itself — not only in external podcast apps — the in-app media players display the item's chapters as a seekable list. Selecting a chapter seeks the player to that start time.

**Why this priority**: A coherence/UX enhancement. The primary channel for chapters is the published feed (Story 1); in-app display is secondary and can ship later without reducing feed-side value.

**Independent Test**: Can be tested by playing a media item that has chapters in the dashboard player and the public share player, and confirming the chapter list is shown and that selecting a chapter seeks to its start time — without re-authoring or re-publishing.

**Acceptance Scenarios**:

1. **Given** a media item with chapters playing in the dashboard media player, **When** the user opens the player, **Then** the chapters are listed by start time.
2. **Given** the chapter list is visible, **When** the user selects a chapter, **Then** playback jumps to that chapter's start time.
3. **Given** a public share page for a media item with chapters, **When** a visitor plays the item, **Then** the same seekable chapter list is available.

---

### Edge Cases

- **Media not yet processed** (duration unknown): chapter authoring and "propose chapters" are unavailable until the duration is known.
- **Exactly 20 chapters**: a 21st is blocked with a clear message.
- **Start time of 0**: valid (the first chapter commonly starts at 0).
- **Start time at or beyond the duration**: rejected.
- **Duplicate or out-of-order start times on entry**: the system orders chapters by start time and rejects duplicate start times.
- **Blank chapter title**: rejected (each chapter requires a title).
- **Removing every chapter**: returns the item to "no chapters" and removes them from the feed.
- **A media item shared across multiple feeds**: its chapters appear in every feed that includes the item.
- **Media re-downloaded or replaced** such that the duration changes: any existing chapter start times that now exceed the new duration are flagged for the user to correct.
- **Video media**: chapters behave identically to audio.
- **Proposal failure**: if transcription or topic segmentation fails, no chapters are published; the user is informed and can retry.
- **Re-proposing**: requesting a new proposal overwrites the prior proposal (it does not touch already-saved/published chapters until the user saves again).
- **Audio with little/no speech** (e.g., music): transcription may yield few or no usable segments; the proposal may be empty or minimal, and the user falls back to manual authoring.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST let the owner of a processed media item add, edit, re-time, and remove chapters, where each chapter consists of a start time and a title.
- **FR-002**: System MUST enforce a hard maximum of 20 chapters per media item and prevent any addition beyond it.
- **FR-003**: Each chapter MUST have a non-empty title and a start time greater than or equal to 0 and less than the media's duration; values outside the duration MUST be rejected.
- **FR-004**: System MUST present chapters in chronological order by start time and MUST reject duplicate start times.
- **FR-005**: Chapter authoring MUST be offered once a newly added media item has been processed (duration known) and MUST remain available at any time thereafter from the item's edit controls.
- **FR-006**: The published podcast feed for any feed containing a chaptered media item MUST include those chapters in a form subscribing podcast apps can display.
- **FR-007**: System MUST provide an on-demand chapter-proposal action that transcribes the media's audio and performs topic segmentation on the transcript to produce content-aligned proposed chapters, with the proposal running in the background (isolated, low priority) and never exceeding 20 chapters. Proposed chapters MUST NOT be published until the user reviews and saves them.
- **FR-008**: Chapter support MUST apply to both audio and video media items.
- **FR-009**: Chapters MUST be optional; a media item with zero chapters MUST behave exactly as it does today.
- **FR-010**: Only the media item's owner MUST be able to create, modify, or delete its chapters.
- **FR-011**: When a media item's duration changes (e.g., re-download), existing chapter start times that exceed the new duration MUST be surfaced to the owner for correction.

### Key Entities *(include if feature involves data)*

- **Media Item** *(existing concept)*: A user's audio or video file in their library. Gains optional chapter support (0–20 chapters). All existing behavior (ownership, processing, feed placement) is unchanged.
- **Chapter** *(new)*: A named, timestamped segment of a single media item, defined by a **start time** (offset from the start of the media) and a **title**. Chapters belong to one media item, are ordered by start time, and are capped at 20 per item. A chapter implicitly ends where the next chapter begins; the final chapter runs to the end of the media.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A user can add, rename, re-time, and remove up to 20 chapters on a processed media item in under 2 minutes for a typical file.
- **SC-002**: No media item in the system can have more than 20 chapters (hard cap verifiable at any time).
- **SC-003**: Every chapter stored has a non-empty title and a start time within the media's duration (100% of stored chapters valid).
- **SC-004**: Chapters authored on a media item appear in its published podcast feed within a single feed refresh, and disappear when removed.
- **SC-005**: For long-form spoken content, an on-demand proposal transcribes the audio and returns content-aligned chapters (no more than 20) without the user typing any timestamps, with longer chapters for longer content.
- **SC-006**: Subscribers see the authored chapters in mainstream podcast apps (verified by feed standards compliance, no app-specific integration).
- **SC-007**: Chapter authoring is available for both audio and video items (parity across media types).
- **SC-008**: While a proposal is being generated, other operations (uploads, downloads, feed edits) are unaffected — the heavy work runs isolated and de-prioritized.

## Assumptions

- A chapter is defined by **start time + title only**. A chapter's end is derived from the next chapter's start (the last chapter runs to the end). Images, links, and per-chapter artwork are out of scope to keep the model simple.
- Chapters describe the audio content itself, so they attach to the underlying media file: if the same file is referenced by more than one library item (duplicates), those items **share** the same chapters — editing them on one updates all. (The exact attachment point is a planning detail.)
- **Authoring requires a known duration**, so "add when the file is added" is offered once processing completes; chapters remain editable any time after.
- **Automatic chapter proposal** is content-aware: the system transcribes the media audio and runs topic segmentation on the transcript to suggest chapters aligned to the actual content. Audio is transcribed on the PodKeep server; only the transcript is sent to an external language-model service for segmentation. The specific transcription engine and language-model provider are implementation choices (resolved in planning), and the provider is configurable so it can be changed without code changes.
- **Proposals are drafts, not publications.** Generated chapters are held for user review and are **not** written to the feed until the user saves. The user can re-request a proposal to overwrite a previous one.
- **Proposal runs in the background, isolated and low priority**, so slow transcription never blocks routine operations.
- The **primary** channel for chapters is the published podcast feed (Story 1); in-app player display (Story 3) is a lower-priority enhancement that can ship later.
- The published feed follows the **podcast community chapter standard** so mainstream podcast apps render chapters without app-specific integration.
- Chapters apply equally to **audio and video** items.
