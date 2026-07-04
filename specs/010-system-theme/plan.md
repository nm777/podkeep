# Implementation Plan: System Theme Preference

**Branch**: `010-system-theme` | **Date**: 2026-07-04 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/010-system-theme/spec.md`

## Summary

Replace the placeholder appearance settings page with a three-way theme selector (Light / Dark / System). The underlying infrastructure already fully supports "System" mode — the `useAppearance` hook resolves `system` to light/dark via `prefers-color-scheme`, listens for OS changes in real time, and persists the choice via cookie and local storage. The only gap is the UI: `settings/appearance.tsx` is currently a 13-line placeholder. This feature replaces it with a proper selector.

## Technical Context

**Language/Version**: PHP 8.4 (Laravel 12), TypeScript (React 19)
**Primary Dependencies**: Laravel Framework, Inertia.js v2, Tailwind CSS v4, Pest PHP v3
**Storage**: Cookie (`appearance`) + localStorage — both already wired by `useAppearance` hook
**Testing**: Pest PHP v3
**Project Type**: Web application (frontend-only change)
**Constraints**: Must reuse existing `useAppearance` hook — no new hooks, middleware, or backend changes

**Key existing assets**:
- `useAppearance` hook (`resources/js/hooks/use-appearance.tsx`) — already exposes `{ appearance, updateAppearance }` with 'system' support
- `HandleAppearance` middleware — already defaults to 'system', shares as Blade variable
- `app.blade.php` inline script — already resolves 'system' to dark/light before paint
- Topbar toggle (`app-topbar.tsx`) — already works for quick light/dark switching
- Existing UI components: Card, Button, Label (shadcn/ui pattern)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **API-First**: No backend changes — this is a frontend-only UI change to an existing page. No new endpoints, data models, or business logic.
- [x] **Media Processing**: No media involved.
- [x] **Test-Driven**: Feature test will verify the appearance settings page renders the three-way selector with the current theme state.
- [x] **Feed Standards**: No feed changes.
- [x] **Security**: Page is already behind `['auth', 'verified', 'approved']` middleware — no changes.
- [x] **Performance**: Client-side only — no server queries or processing changes.

## Project Structure

### Source Code (repository root)

```text
src/
├── resources/js/
│   ├── pages/settings/
│   │   └── appearance.tsx     # EDIT — replace placeholder with 3-way selector
│   └── components/
│       └── (existing UI components reused — Card, Button, Label)
└── tests/
    └── Feature/
        └── AppearanceSettingsTest.php  # NEW — verify selector renders

```

**Structure Decision**: Single file edit (`appearance.tsx`) plus one test file. No new directories, components, hooks, or backend files. The `useAppearance` hook already handles all theme logic.

No data-model.md, contracts/, or research.md needed for this feature — there are no new entities, interfaces, or unknowns. The investigation during the spec phase confirmed the infrastructure is complete.
