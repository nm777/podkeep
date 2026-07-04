# Feature Specification: System Theme Preference

**Feature Branch**: `010-system-theme`
**Created**: 2026-07-04
**Status**: Draft
**Input**: User description: "I have a light and dark mode. I'd like to add an option for following the system settings, so if the user has their system configured to change with the time of day, the site does so as well."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Three-Way Theme Selector (Priority: P1)

A user currently has two ways to control the theme: a quick-toggle button in the top bar (cycles between light and dark) and an empty appearance settings page. The user wants a third option — "follow system" — where the site automatically uses light or dark mode based on the operating system's preference. When the OS switches (e.g., at sunset via a scheduled lighting change), the site should follow immediately without requiring the user to toggle anything.

The user can choose between three modes from the appearance settings page: Light, Dark, or System. Selecting "System" makes the site respond to the OS theme preference in real time. The top bar toggle continues to provide a quick light/dark switch for convenience, but the settings page is where the "System" option lives — since it's a preference, not a quick toggle.

**Why this priority**: This is the complete feature — a three-way selector in settings that exposes the "System" mode the user is asking for. The underlying infrastructure already handles system mode; the gap is purely that the UI doesn't expose it.

**Independent Test**: Can be fully tested by opening the appearance settings page, selecting "System", and verifying the site theme matches the OS preference. Changing the OS preference (or simulating it in browser dev tools) should cause the site to update in real time.

**Acceptance Scenarios**:

1. **Given** a user is on the appearance settings page, **When** they select "System", **Then** the site theme switches to match the operating system's current light/dark preference.
2. **Given** the user has selected "System" mode, **When** the operating system switches from light to dark (e.g., at sunset), **Then** the site automatically switches to dark mode without a page reload.
3. **Given** the user has selected "System" mode, **When** they navigate to the top bar and click the theme toggle, **Then** the site switches to the opposite of the current visible theme (e.g., dark to light), and the mode changes from "System" to the explicitly chosen theme.
4. **Given** a new user visits the site for the first time, **When** no theme preference has been set, **Then** the site defaults to "System" mode, following the OS preference.
5. **Given** the user previously selected "Light" or "Dark", **When** they return to the appearance settings page, **Then** the currently selected mode is highlighted, and they can switch to "System" at any time.

---

### Edge Cases

- What happens when the user selects "System" but the browser doesn't support `prefers-color-scheme`? The site falls back to light mode (the default for unsupported queries).
- What happens when the user selects "System" and then closes and reopens the browser? The preference persists via cookie and local storage, so "System" mode is restored on the next visit.
- What happens when two browser tabs are open and the user changes the theme in one tab? Each tab maintains its own state during the session, but both will have the correct preference on the next page load (via cookie).
- What happens when the user uses the top bar toggle while in "System" mode? The toggle switches to an explicit light or dark choice, overriding "System" mode. This is expected behavior — the user can return to "System" from the settings page.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a three-way theme mode selector on the appearance settings page with options: Light, Dark, and System.
- **FR-002**: System MUST default to "System" mode for new users who have not yet chosen a theme preference.
- **FR-003**: When "System" mode is selected, the site MUST match the operating system's light/dark preference in real time, updating immediately when the OS preference changes.
- **FR-004**: The theme mode selection MUST persist across browser sessions via both cookie (for server-side rendering) and local storage (for immediate client-side reads).
- **FR-005**: The appearance settings page MUST show the currently selected mode as highlighted/active when the page loads.
- **FR-006**: The top bar quick-toggle MUST continue to work, switching between the visible light/dark state. Using the toggle while in "System" mode MUST switch to the explicit mode chosen.
- **FR-007**: The theme mode selector MUST be accessible via the avatar dropdown menu (which already links to the appearance settings page) — no new navigation entry points are needed.
- **FR-008**: The appearance settings page MUST replace the current placeholder text with the actual three-way selector.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can switch between Light, Dark, and System modes in under 5 seconds from the appearance settings page.
- **SC-002**: When in System mode, the site theme matches the OS preference within 1 second of the OS changing.
- **SC-003**: 100% of new users see the correct theme (matching their OS preference) on first visit with no action required.
- **SC-004**: The theme preference persists correctly across browser restarts, page reloads, and navigation.
