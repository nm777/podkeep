# Tasks: Search

**Input**: Design documents from `/specs/013-search/`  
**Prerequisites**: plan.md, spec.md, research.md, quickstart.md

**Tests**: No backend tests — feature is purely client-side React filtering.

**Organization**: Tasks grouped by user story for independent implementation.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Frontend**: `src/resources/js/components/`, `src/resources/js/hooks/`, `src/resources/js/pages/`
- **Tests**: Manual verification (no backend test changes)

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create the reusable building blocks all user stories depend on

- [ ] T001 Create debounce hook `useDebouncedValue` (returns a debounced version of a value after 150ms delay) in `src/resources/js/hooks/use-debounced-value.ts`
- [ ] T002 Create `SearchInput` component (text input with search icon on left, clear/X button when non-empty, accepts `value`, `onChange`, `placeholder` props) in `src/resources/js/components/search-input.tsx`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: No additional foundational work — the setup phase covers shared components

**Checkpoint**: SearchInput and debounce hook ready. User story implementation can begin.

---

## Phase 3: User Story 1 — Search the Library (Priority: P1) 🎯 MVP

**Goal**: Real-time title filtering on the Library tab of the dashboard.

**Independent Test**: Open the dashboard Library tab, type a partial item title, verify only matching items show. Clear search, verify all items return.

- [ ] T003 [US1] Add search state (`useState`) and `SearchInput` to the Library tab section of `src/resources/js/pages/dashboard.tsx`, filtering `libraryItems` by title using the debounced value. Show a "No results" message when filter returns empty. Reset search when switching tabs.

**Checkpoint**: Library search works independently.

---

## Phase 4: User Story 2 — Search Within a Feed's Item List (Priority: P2)

**Goal**: Real-time title filtering on the feed edit page item list without affecting sequence order.

**Independent Test**: Open a feed with 10+ items, type a partial title, verify only matching items show. Clear search, verify all items return in original order.

- [ ] T004 [US2] Add search state and `SearchInput` above the item list in `src/resources/js/pages/feeds/edit.tsx`. Create a filtered display array (`data.items.filter(...)`) for rendering — do NOT modify `data.items` itself. Show "No results" when empty. Ensure drag-and-drop still works on visible items and sequence order is preserved when search is cleared.

**Checkpoint**: Feed item search works without disrupting ordering.

---

## Phase 5: User Story 3 — Search the Feed List (Priority: P3)

**Goal**: Real-time title filtering on the Feeds tab of the dashboard.

**Independent Test**: Open the dashboard Feeds tab, type a partial feed title, verify only matching feeds show. Clear, verify all return.

- [ ] T005 [US3] Add search state and `SearchInput` to the Feeds tab section of `src/resources/js/pages/dashboard.tsx`, filtering `feeds` by title using the debounced value. Show a "No results" message when empty. Reset search when switching tabs.

**Checkpoint**: Feed list search works independently.

---

## Phase 6: Polish & Cross-Cutting Concerns

- [ ] T006 Run `npm run build` to verify frontend compiles
- [ ] T007 Run fallow on changed files and address findings

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — T001 and T002 can run in parallel
- **User Stories (Phase 3-5)**: All depend on Setup completion
  - US1 and US3 both modify `dashboard.tsx` — must run sequentially or by the same agent
  - US2 modifies `edit.tsx` — fully independent from US1/US3
  - US2 can run in parallel with US1
- **Polish (Phase 6)**: Depends on all user stories being complete

### Parallel Opportunities

- T001 and T002 (Setup) can run in parallel
- US2 (edit page) can run in parallel with US1 (dashboard)
- US1 and US3 share the same file (`dashboard.tsx`) — do sequentially

---

## Implementation Strategy

### MVP First (User Story 1)

1. Complete Setup (debounce hook + SearchInput component)
2. Complete US1 (Library search)
3. **STOP and VALIDATE**: Type a search on the Library tab, verify filtering works

### Incremental Delivery

1. Setup → Shared components ready
2. US1 → Library search → Deploy/Demo (MVP!)
3. US2 → Feed edit search → Deploy
4. US3 → Feed list search → Deploy
5. Polish → Build verified
