# Feature Specification: REST API with API Key Authentication

**Feature Branch**: `008-rest-api-keys`  
**Created**: 2026-07-03  
**Status**: Draft  
**Input**: User description: "I want to create a REST API for this app. It should allow users to create and revoke API keys and then perform all the things they can do in the UI via API calls."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - API Key Management (Priority: P1)

An approved, verified user wants to generate a personal API key from the settings page so they can authenticate programmatic requests without sharing their login credentials. They can name the key (e.g., "CI/CD uploads"), view it once upon creation, and later revoke it when it is no longer needed. The user can also see a list of their active keys (with creation date and last-used timestamp, but never the secret value again) and revoke any key individually.

**Why this priority**: API keys are the foundational prerequisite for every other API endpoint. Without the ability to create and present a valid key, no API functionality is accessible. This is the gating user story for the entire feature.

**Independent Test**: Can be fully tested by having a user create an API key, verify it is shown exactly once, confirm subsequent API requests using that key are authenticated, and then revoke it and confirm requests are rejected.

**Acceptance Scenarios**:

1. **Given** an approved, verified user is on the settings page, **When** they create a new API key with a name, **Then** the system displays the full key value exactly once and stores only a hashed version for future comparison.
2. **Given** a user has one or more API keys, **When** they view their key list, **Then** they see each key's name, creation date, and last-used timestamp, but never the secret value.
3. **Given** a user has an active API key, **When** they revoke it, **Then** all subsequent API requests using that key are immediately rejected.
4. **Given** a user has made API requests using a valid key, **When** they view their key list, **Then** the last-used timestamp for that key reflects the most recent request.

---

### User Story 2 - Podcast Feed Management via API (Priority: P2)

A user wants to use their API key to create new podcast feeds, list their existing feeds, update feed details (title, description, website URL, visibility), and delete feeds — all through structured API requests that return JSON instead of HTML redirects.

**Why this priority**: Feeds are the top-level organizational unit in the app. Users need to create feeds before they can attach media. This is the most common API operation after authentication and directly mirrors the primary UI flow.

**Independent Test**: Can be fully tested by authenticating with an API key, creating a feed via the API, listing feeds to confirm it appears, updating its title, and deleting it.

**Acceptance Scenarios**:

1. **Given** a user provides a valid API key, **When** they send a request to create a feed with a title, **Then** the system creates the feed and returns its details (including generated slug and token) as JSON.
2. **Given** a user has existing feeds, **When** they request the feed list endpoint, **Then** the system returns all feeds owned by that user as a JSON array.
3. **Given** a user owns a feed, **When** they send an update request with new field values, **Then** the system updates the feed and returns the updated resource as JSON.
4. **Given** a user owns a feed, **When** they send a delete request, **Then** the system removes the feed and returns a confirmation with an appropriate status code.

---

### User Story 3 - Media Upload & Library Management via API (Priority: P3)

A user wants to upload media files (mp3, mp4, m4a, wav, ogg) directly through the API, list their library items, view processing status, update item metadata (title, description, published date), and delete items. This mirrors the upload and library management capabilities available in the UI.

**Why this priority**: Uploading and managing media is the core value of the app. This user story delivers the primary use case that motivated the API request — allowing terminal-based or automated media ingestion.

**Independent Test**: Can be fully tested by authenticating, uploading an mp3 file via the API, polling for processing completion, verifying the item appears in the library list, updating its title, and deleting it.

**Acceptance Scenarios**:

1. **Given** a user provides a valid API key, **When** they upload a valid media file with a title, **Then** the system accepts the file, creates a library item, and returns its details and processing status as JSON.
2. **Given** a user uploads a file via URL instead of direct upload, **When** the system processes it, **Then** the item is created and the processing status is returned.
3. **Given** a user has library items, **When** they request the library list endpoint, **Then** the system returns all items with their metadata and current processing status as JSON.
4. **Given** a user owns a library item, **When** they send an update request with new metadata, **Then** the system updates the item and returns the updated resource as JSON.
5. **Given** a user owns a library item, **When** they send a delete request, **Then** the system removes the item and its associated media file (if no other items reference it) and returns a confirmation.

---

### User Story 4 - Feed Item Management via API (Priority: P4)

A user wants to attach library items to feeds, remove items from feeds, and reorder items within a feed — all through the API. This allows full programmatic control over podcast episode organization.

**Why this priority**: While feeds and media can be created independently, attaching media to feeds is what makes a podcast consumable via RSS. This completes the end-to-end workflow but depends on the prior stories.

**Independent Test**: Can be fully tested by creating a feed and a library item via the API, then attaching the item to the feed, reordering it, and removing it.

**Acceptance Scenarios**:

1. **Given** a user owns a feed and a completed library item, **When** they send a request to attach the item to the feed, **Then** the system links them and returns the updated feed with its items.
2. **Given** a feed has multiple items, **When** the user sends a reorder request with new sequence values, **Then** the system updates the ordering and returns confirmation.
3. **Given** an item is attached to a feed, **When** the user sends a request to remove it, **Then** the system unlinks the item from the feed and returns confirmation.

---

### User Story 5 - Media Processing Operations via API (Priority: P5)

A user wants to retry processing on failed library items and trigger redownloads of media from their original source URLs — all through the API, so automated pipelines can recover from transient failures without manual UI interaction.

**Why this priority**: These are recovery operations that improve resilience but are not needed for the happy path. They mirror existing UI buttons and are straightforward API translations.

**Independent Test**: Can be fully tested by creating a library item that fails processing, then triggering a retry via the API and confirming the item's status changes to processing.

**Acceptance Scenarios**:

1. **Given** a user owns a library item in a failed processing state, **When** they send a retry request, **Then** the system re-queues processing and returns the updated item status.
2. **Given** a user owns a library item with a source URL, **When** they send a redownload request, **Then** the system re-downloads the media from the original source and returns the updated item status.
3. **Given** a library item is not in a failed state, **When** the user sends a retry request, **Then** the system returns an appropriate error indicating retry is only available for failed items.

---

### Edge Cases

- What happens when a user provides an invalid, expired, or revoked API key? The system rejects the request with a clear authentication error and does not leak any resource information.
- What happens when a user attempts to access, modify, or delete another user's feeds or library items? The system denies access with an authorization error.
- What happens when a user uploads a file exceeding the maximum allowed size (500 MB)? The system rejects the upload with a descriptive error before consuming excessive resources.
- What happens when a user uploads a file with an unsupported format (not mp3/mp4/m4a/wav/ogg)? The system rejects it with a validation error listing accepted formats.
- What happens when a user exceeds the rate limit on API requests? The system returns a rate-limit error with headers indicating when the user can retry.
- What happens when a user attempts to create a feed with a duplicate slug (same title as an existing feed)? The system generates a unique slug and succeeds, mirroring current UI behavior.
- What happens when an API request is made with a valid key but the user account has been deactivated or de-approved? The system rejects all requests for that user.
- What happens when a user tries to attach a library item that is still processing to a feed? The system queues the attachment to complete once processing finishes, mirroring current UI behavior.
- What happens when concurrent API requests attempt to modify the same resource simultaneously? The system handles them safely without data corruption, using appropriate concurrency controls.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a way for approved, verified users to create named API keys from the settings page, displaying the full key value exactly once at creation time.
- **FR-002**: System MUST store API keys in a hashed form and never expose the plaintext key value after initial creation.
- **FR-003**: System MUST allow users to view a list of their API keys showing name, creation date, and last-used timestamp, without the secret value.
- **FR-004**: System MUST allow users to revoke (delete) any of their API keys at any time, with revocation taking effect immediately for all subsequent requests.
- **FR-005**: System MUST authenticate all API endpoints by validating a bearer token (API key) provided in the request header.
- **FR-006**: System MUST reject all API requests that provide a missing, invalid, or revoked API key with a clear, structured error response.
- **FR-007**: System MUST provide API endpoints for listing, creating, updating, and deleting podcast feeds that return JSON responses with resource details.
- **FR-008**: System MUST enforce that users can only access, modify, or delete feeds and library items they own.
- **FR-009**: System MUST provide an API endpoint for uploading media files directly (multipart upload) that accepts mp3, mp4, m4a, wav, and ogg files up to 500 MB.
- **FR-010**: System MUST provide an API endpoint for adding media via URL that downloads and processes the file server-side.
- **FR-011**: System MUST provide API endpoints for listing library items (with processing status), updating item metadata, and deleting items.
- **FR-012**: System MUST process all uploaded and downloaded media files asynchronously through queued jobs, returning a processing status in API responses so clients can poll for completion.
- **FR-013**: System MUST provide API endpoints for attaching library items to feeds, removing items from feeds, and reordering items within a feed.
- **FR-014**: System MUST provide API endpoints for retrying failed media processing and triggering redownloads from original source URLs.
- **FR-015**: System MUST apply rate limiting to API requests to prevent abuse, with limits communicated via response headers.
- **FR-016**: System MUST return structured JSON error responses with appropriate HTTP status codes for all error conditions (authentication failure, authorization denial, validation errors, not found, rate limit exceeded).
- **FR-017**: System MUST ensure API key management is only accessible to approved, verified users, matching the access requirements of the existing web interface.
- **FR-018**: System MUST update the last-used timestamp on an API key each time it is used to authenticate a request.

### Key Entities *(include if feature involves data)*

- **API Key**: A named credential that authenticates a user for API access. Key attributes: name (user-assigned label), hashed secret value (stored, never re-displayed), plaintext value (shown only once at creation), creation date, last-used timestamp, active/revoked status. Belongs to one User.
- **Feed (existing)**: A podcast container. API exposes: title, description, website URL, visibility flag, auto-generated slug, and associated items. Owned by one User.
- **Library Item (existing)**: A media/episode entry. API exposes: title, description, source type (upload/url/youtube), source URL, published date, processing status, and associated media file details. Owned by one User.
- **Media File (existing)**: The actual audio/video artifact. API exposes: file format, file size, duration, source URL, and public access URL. Linked to Library Items.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can create a new API key in under 30 seconds from the settings page.
- **SC-002**: Users can create a podcast feed and upload a media file entirely via API in under 1 minute (excluding async processing time).
- **SC-003**: 100% of UI-accessible operations for feeds, library items, and feed management are available via equivalent API endpoints.
- **SC-004**: API authentication rejects 100% of requests with invalid, expired, or revoked API keys.
- **SC-005**: API error responses return within 2 seconds for all client-side errors (authentication, validation, authorization).
- **SC-006**: Users can programmatically complete the full workflow (create key, create feed, upload media, attach to feed, verify via RSS) with zero UI interaction.
- **SC-007**: An automated client can recover from a failed media upload by detecting the failure via API status and triggering a retry, all without manual intervention.
