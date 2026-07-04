# podkeep Development Guidelines

Auto-generated from all feature plans. Last updated: 2026-05-25

## Active Technologies
- PHP 8.4 (Laravel 12), TypeScript (React 19) + Laravel Framework, Inertia.js v2, Tailwind CSS v4, Pest PHP v3, Laravel Sanctum v4 (NEW — for personal access tokens) (008-rest-api-keys)
- MySQL 8.0+ (SQLite for tests), database-backed queues, local `public` disk for media files (008-rest-api-keys)
- Cookie (`appearance`) + localStorage — both already wired by `useAppearance` hook (010-system-theme)

- PHP 8.4 (Laravel 12), TypeScript (React 19) + Laravel Framework, Inertia.js v2, Tailwind CSS v4, Pest PHP v3 (007-podcast-share-player)

## Project Structure

```text
src/
tests/
```

## Commands

npm test && npm run lint

## Code Style

PHP 8.4 (Laravel 12), TypeScript (React 19): Follow standard conventions

## Recent Changes
- 010-system-theme: Added PHP 8.4 (Laravel 12), TypeScript (React 19) + Laravel Framework, Inertia.js v2, Tailwind CSS v4, Pest PHP v3
- 009-feed-episode-order: Added PHP 8.4 (Laravel 12), TypeScript (React 19) + Laravel Framework, Inertia.js v2, Tailwind CSS v4, Pest PHP v3, Laravel Sanctum v4
- 008-rest-api-keys: Added PHP 8.4 (Laravel 12), TypeScript (React 19) + Laravel Framework, Inertia.js v2, Tailwind CSS v4, Pest PHP v3, Laravel Sanctum v4 (NEW — for personal access tokens)


<!-- MANUAL ADDITIONS START -->

## Task Execution Rules

- For any JavaScript change, when the task is complete, run [fallow](https://github.com/fallow-rs/fallow) and address any findings before proceeding.
- For any PHP change, when the task is complete, run [PHPStan](https://github.com/phpstan/phpstan) and address any findings before proceeding.
- When everything is clean, if there is a task list, mark the task complete, create a new commit for the change, then proceed to the next task.
- Unless instructed otherwise, iterate until all tasks are complete and the feature is fully finished.
- If there are any questions along the way, choose the most likely choice and continue. Don't stop and prompt me.

### Subagent Scope

When dispatching work to subagents, the **orchestrating agent** retains responsibility for the rules above. Subagents should:

- Complete **only their assigned task** and return immediately — do not iterate to other tasks.
- **Not commit** changes — leave that to the orchestrator.
- **Not run linting or static analysis** unless explicitly asked — the orchestrator runs quality checks after the work is returned.
- Report any blockers or decisions made back to the orchestrator in the task result.

### Docker Deployment Architecture

**Do not run `docker compose` commands from this repo directory.** This dev path and the production deployment share the same Docker Compose project name (`podkeep`), so any `docker compose` command here affects production containers.

**Production** lives at `/home/nate/Documents/docker/podkeep/`:
- Uses pre-built images (`podkeep-app:latest`, `podkeep-web:latest`) with source code baked into the image at build time.
- Only `storage/`, `database.sqlite`, and `.env` are volume-mounted. Source code and `public/build/` come from the image.
- The user rebuilds images manually — do not attempt it yourself.

**Development** lives at `/home/nate/src/podkeep/`:
- The `docker-compose.yml` uses `target: dev` which does NOT copy source into the image. The bind mount (`./src:/var/www/html`) is broken — the running container has stale files from its original creation, not current source.
- **Never run `npm run build` inside the running container** — it builds from stale files, not current source, and produces a Vite manifest that doesn't match the assets served by nginx.
- To run PHP commands (tests, artisan, phpstan), the orchestrator must first sync changed files into the container via `docker compose cp src/<path> app:/var/www/html/<path>` (from the production compose directory).
- Node/npm/fallow are not installed by default — they must be installed via `apk add --no-cache nodejs npm` inside the container after each restart.

**The correct workflow for frontend changes:**
1. Edit files on the host.
2. Sync to container, run tests/tools as needed.
3. Commit the source changes.
4. The user rebuilds the Docker images and redeploys — the Dockerfile's `frontend` stage runs `npm run build` during image build, producing correct assets from current source.

<!-- MANUAL ADDITIONS END -->
