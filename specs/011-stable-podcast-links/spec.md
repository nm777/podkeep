# Feature Specification: Stable Podcast Links (Links Survive Renames)

**Feature Branch**: `011-stable-podcast-links`  
**Created**: 2026-07-10  
**Status**: Draft  
**Input**: User description: "Renaming podcasts shouldn't break the links that already exist to them."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Rename Keeps RSS Subscriptions Alive (Priority: P1)

A podcast owner has already shared their podcast's RSS feed link with podcast apps (Apple Podcasts, Google, Overcast, etc.) and with listeners. Later, they rename the podcast. Every previously shared RSS link must keep working: existing subscribers must continue receiving new episodes without interruption and without having to re-subscribe.

**Why this priority**: This is the core promise of the feature. A broken RSS link silently severs every existing subscriber — listeners stop getting episodes and may never realize the podcast still exists. Podcast-industry practice treats an RSS feed URL as a permanent commitment; changing it is the single most destructive action a podcaster can take against their own audience.

**Independent Test**: Create a podcast, note its RSS link, subscribe to that link in a podcast client, then rename the podcast. Verify the same RSS link still serves a valid feed listing the episodes, and that new episodes continue to appear for the existing subscription.

**Acceptance Scenarios**:

1. **Given** a podcast has a public RSS link that is subscribed to, **When** the owner renames the podcast, **Then** the original RSS link still returns a valid feed with all episodes listed.
2. **Given** a private podcast accessed via a link with its access token, **When** the owner renames the podcast, **Then** the original link (with its token) still grants access to the feed.
3. **Given** a podcast has been renamed, **When** a podcast app polls the original RSS link, **Then** it receives the current feed with the new display name and existing episodes are not duplicated.
4. **Given** a podcast is renamed multiple times, **When** any link shared at any point in the past is opened, **Then** it still resolves to the current podcast.

---

### User Story 2 - Editing Details Never Changes the Link (Priority: P2)

A podcast owner edits non-name details of their podcast — fixing a description typo, changing cover artwork, or toggling between public and private visibility — without changing the name. The podcast's public link must remain byte-for-byte identical, because every edit currently risks rewriting the link.

**Why this priority**: This protects links from collateral damage. Even users who never rename their podcast can break their own shared links today by saving an unrelated edit, because the link is regenerated on every save. This story ensures link stability is not tied to the name field at all.

**Independent Test**: Create a podcast, capture its exact public link, then make several non-name edits (description, cover, visibility). Verify the public link is unchanged after each save.

**Acceptance Scenarios**:

1. **Given** a podcast with an established public link, **When** the owner edits only the description and saves, **Then** the public link is unchanged.
2. **Given** a podcast with an established public link, **When** the owner changes cover artwork and saves, **Then** the public link is unchanged.
3. **Given** a podcast with an established public link, **When** the owner toggles it from public to private (or vice versa) and saves, **Then** the public link is unchanged (only access requirements change).

---

### User Story 3 - Consistent Renaming Across All Methods (Priority: P3)

A podcast owner can rename their podcast through whichever interface they choose — the web management screen or any connected API client. Regardless of the method used, the rename takes full effect (the new name appears everywhere listeners see it) while the podcast's existing link stays stable.

**Why this priority**: Guarantees the feature can't be bypassed by the entry point. It also closes a current inconsistency where one method changes the link and another does not, so the behavior must be unified so the owner never has to remember which way is "safe."

**Independent Test**: Create a podcast, rename it via the web screen, confirm the link is stable and the new name shows on the share page. Repeat a rename via the API and confirm the same outcome.

**Acceptance Scenarios**:

1. **Given** a podcast renamed through the web management screen, **When** a listener opens the original link, **Then** the share page displays the new name and the link is unchanged.
2. **Given** a podcast renamed through the API, **When** a listener opens the original link, **Then** the share page displays the new name and the link is unchanged.
3. **Given** the same podcast is renamed through two different methods in sequence, **When** the original link is opened after each rename, **Then** it resolves correctly and shows the latest name both times.

---

### Edge Cases

- A podcast is renamed repeatedly (three or more times): do links shared before the first rename still resolve?
- Two podcasts owned by the same user start with different names but one is later renamed to match the other's name: the rename must not collide with or hijack the other podcast's established link.
- A podcast is renamed to a name containing special characters or non-Latin scripts: the established link must be unaffected (it was set at creation).
- A podcast that has never been shared (kept private from creation) is renamed: its link must still behave stably so that any future sharing is safe.
- A podcast's RSS link is cached or held by an external podcatcher that only re-checks on a long interval: the link must continue serving the feed indefinitely after a rename.
- An owner attempts to rename a podcast they do not own: the action must be blocked (authorization), and no link is affected.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST establish each podcast's public link (RSS feed link and share-page link) at the time of creation and treat it as permanent thereafter.
- **FR-002**: The system MUST ensure that changing a podcast's display name does not alter or invalidate its public link.
- **FR-003**: The system MUST ensure that editing any non-name podcast detail (description, cover artwork, visibility, episode ordering, website link) does not alter its public link.
- **FR-004**: The system MUST reflect a renamed podcast's new display name on all listener-facing surfaces (RSS feed content, share page, management views) while keeping the public link unchanged.
- **FR-005**: The system MUST ensure that renaming a podcast does not interrupt existing podcast-app subscriptions, cause episodes to be duplicated, or require listeners to re-subscribe.
- **FR-006**: The system MUST enforce that only the podcast owner can rename a podcast.
- **FR-007**: The system MUST prevent a rename or new creation from colliding with, or hijacking, another podcast's already-established link.
- **FR-008**: The system MUST apply identical link-stability behavior regardless of which interface (web management screen or API) is used to rename or edit a podcast.

### Key Entities *(include if feature involves data)*

- **Podcast (Feed)**: The show a user manages and shares. Has a *display name* (human-readable, changeable at any time) and a *permanent public link identifier* (established at creation, never changes). The display name and the link identifier are independent: changing one never changes the other.
- **Podcast Link**: The public address a listener or podcast app uses to reach a podcast's RSS feed or share page. Composed of a permanent opaque identifier set at creation; remains valid for the lifetime of the podcast regardless of name changes.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of RSS feed links shared before a podcast rename remain valid and serve the correct, up-to-date feed afterward.
- **SC-002**: 100% of share-page links shared before a podcast rename remain valid afterward.
- **SC-003**: 0% of podcast-app subscriptions are interrupted, lost, or require re-subscription as a result of a rename.
- **SC-004**: Editing any non-name podcast detail leaves the public link byte-for-byte unchanged in 100% of cases.
- **SC-005**: After a rename, the new display name appears in the RSS feed and on the share page for 100% of viewers within the normal feed-update window.
- **SC-006**: Link stability holds in 100% of renames regardless of which interface performed the rename.

## Assumptions

- Podcast-industry standard practice treats an RSS feed URL as permanent and immutable; podcast apps cache the URL they were given and a change effectively severs the subscription. Accordingly, the public link is fixed at creation and never rewritten — even to "match" a new name. This is the strongest guarantee that existing links never break, and it mirrors how major podcast hosts behave.
- "Links that already exist" means the RSS feed link, the share-page link, and any external podcast-app subscription that holds the RSS link. It does not include the internal management screen (which uses a private, non-shareable address).
- If an owner genuinely needs a different public link in the future (e.g., a rebrand with a vanity URL), that is a separate, opt-in capability outside the scope of this feature; this feature only guarantees that ordinary renames never break existing links.
