---

description: "Task list for System Theme Preference feature"
---

# Tasks: System Theme Preference

**Input**: Design documents from `/specs/010-system-theme/`
**Prerequisites**: plan.md, spec.md

**Tests**: MANDATORY per constitution — all features require test coverage.

**Organization**: Tasks are grouped by user story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to
- Include exact file paths in descriptions

## Path Conventions

- **Frontend**: `src/resources/js/pages/`, `src/resources/js/hooks/`
- **Tests**: `src/tests/Feature/`

---

## Phase 1: User Story 1 — Three-Way Theme Selector (Priority: P1) 🎯 MVP

**Goal**: Replace the placeholder appearance settings page with a three-way theme selector (Light / Dark / System) that uses the existing `useAppearance` hook.

**Independent Test**: Open the appearance settings page, verify three options (Light, Dark, System) are visible with the current mode highlighted. Select "System" and verify the site follows the OS theme.

**Note**: The `useAppearance` hook (`src/resources/js/hooks/use-appearance.tsx`) already fully supports 'system' mode — real-time OS preference detection, cookie/localStorage persistence, media query listener. The topbar toggle already works. No backend changes needed. This is purely replacing the placeholder UI.

### Tests for User Story 1 (MANDATORY per constitution) ⚠️

- [X] T001 [P] [US1] Feature test for appearance settings page in `src/tests/Feature/AppearanceSettingsTest.php` — test that GET `/settings/appearance` renders successfully (assertOk + assertInertia component 'settings/appearance'), test that authenticated approved users can access it, test that unauthenticated users are redirected

### Implementation for User Story 1

- [X] T002 [US1] Replace placeholder appearance settings page in `src/resources/js/pages/settings/appearance.tsx` — remove the "use the topbar" placeholder text, add a three-way selector using `useAppearance()` hook (which returns `{ appearance, updateAppearance }`). Render three options as clickable cards or a segmented control: "Light" (Sun icon), "Dark" (Moon icon), "System" (Monitor icon, with description "Follows your system preference"). Highlight the currently active mode. Use existing UI components (Card, Button, Label) from `@/components/ui/`. Use `AppLayout` and `<Head title="Appearance" />` as before. Import icons from `lucide-react`.

**Checkpoint**: The appearance settings page now shows a proper three-way theme selector. Users can choose "System" to follow their OS preference.

---

## Phase 2: Polish & Cross-Cutting Concerns

- [X] T003 [P] Run `npx fallow dead-code` to check for any new unused exports introduced by the appearance page change
- [X] T004 Run full test suite via `php artisan test --no-interaction` to verify no regressions

---

## Dependencies & Execution Order

### Phase Dependencies

- **User Story 1 (Phase 1)**: No dependencies — can start immediately. T001 (test) and T002 (implementation) can be written in parallel (different files).
- **Polish (Phase 2)**: Depends on US1 being complete.

### Within User Story 1

- T001 (test) and T002 (implementation) touch different files — can be written in parallel.
- T002 depends conceptually on understanding the `useAppearance` hook API, which is documented in the plan.

---

## Implementation Strategy

### MVP (Single Story)

1. Write test (T001) — verify the page renders
2. Implement the selector UI (T002) — replace placeholder
3. Run Fallow + full test suite (T003-T004)
4. Done — users can now select "System" from the appearance settings page

---

## Notes

- This is the smallest feature in the project — one UI file edit + one test file
- The `useAppearance` hook already handles all theme logic (system mode, persistence, real-time updates)
- No backend changes, no migrations, no new components, no new hooks
- The topbar toggle continues to work as-is (quick light/dark switch)
- Run `npm run build` after the frontend change so the Vite manifest includes the updated page
