# podkeep Development Guidelines

Auto-generated from all feature plans. Last updated: 2026-07-12

## Active Technologies
- PHP 8.4 (Laravel 13), TypeScript (React 19+) + Laravel Framework 13, Inertia.js v3, Tailwind CSS v4, Pest PHP v4
- PostgreSQL (production), SQLite (tests), local `public` disk for media files
- Laravel Sanctum v4 (personal access tokens for API)
- yt-dlp + ffmpeg (in production image, for YouTube download and video-to-audio conversion)
- PHP 8.4 (Laravel 13), TypeScript (React 19+) + Laravel 13, Inertia.js v3, Tailwind CSS v4, Pest PHP v4, lucide-react; existing `rss.blade.php` feed view + DOMDocument XML validation (016-chapter-markers)
- PostgreSQL (production) / SQLite (tests); new `chapters` table; local `public` disk for media (unchanged) (016-chapter-markers)
- PHP 8.4 (Laravel 13), TypeScript (React 19+) + Laravel 13, Inertia.js v3, Tailwind CSS v4, Pest PHP v4, lucide-react; existing `rss.blade.php` + DOMDocument XML validation; **whisper.cpp** (new, transcription); OpenAI-compatible LLM via `Http` (new) (016-chapter-markers)
- PostgreSQL (production) / SQLite (tests); new `chapters` table + nullable generation-state columns on `media_files` (`transcript`, `chapter_generation_status`, `chapter_proposal`, `chapter_generation_error`); local `public` disk for media (unchanged) (016-chapter-markers)
- PostgreSQL (production) / SQLite (tests); existing `jobs` + `failed_jobs` tables; new `completed_job_log` table (P3) (018-admin-queue-panel)

## Project Structure

## Paths

- The repository root is the current workspace.
- The Laravel application root is `./src`.
- Resolve application files as `./src/app/...`, `./src/routes/...`, and `./src/tests/...`.
- Never expand `src/...` relative to `~` or any parent directory.
- Before reading application files, verify the workspace root contains both `AGENTS.md` and `src/composer.json`.

```text
./src/                        # Laravel application root
├── app/
│   ├── Console/Commands/
│   ├── Enums/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Jobs/
│   ├── Models/
│   └── Services/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/                   # React frontend (Inertia.js)
│   │   ├── components/
│   │   ├── hooks/
│   │   ├── pages/
│   │   └── types/
│   └── views/                # Blade (RSS template)
└── tests/
    ├── Feature/
    └── Unit/
```

## Commands

All tooling runs in ephemeral Docker containers (see Manual Additions below for details).

## Code Style

PHP 8.4 (Laravel 13), TypeScript (React 19+): Follow standard conventions

## Recent Changes
- 018-admin-queue-panel: Added PHP 8.4 (Laravel 13), TypeScript (React 19+) + Laravel 13, Inertia.js v3, Tailwind CSS v4, Pest PHP v4
- 016-chapter-markers: Added PHP 8.4 (Laravel 13), TypeScript (React 19+) + Laravel 13, Inertia.js v3, Tailwind CSS v4, Pest PHP v4, lucide-react; existing `rss.blade.php` + DOMDocument XML validation; **whisper.cpp** (new, transcription); OpenAI-compatible LLM via `Http` (new)
- 016-chapter-markers: Added PHP 8.4 (Laravel 13), TypeScript (React 19+) + Laravel 13, Inertia.js v3, Tailwind CSS v4, Pest PHP v4, lucide-react; existing `rss.blade.php` feed view + DOMDocument XML validation


<!-- MANUAL ADDITIONS START -->

## Task Execution Rules

- For any JavaScript change, when the task is complete, run [fallow](https://github.com/fallow-rs/fallow) and address any findings before proceeding.
- For any PHP change, when the task is complete, run [PHPStan](https://github.com/phpstan/phpstan) and address any findings before proceeding.
- When everything is clean, if there is a task list, mark the task complete, create a new commit for the change, then proceed to the next task.
- Unless instructed otherwise, iterate until all tasks is complete and the feature is fully finished.
- If there are any questions along the way, choose the most likely choice and continue. Don't stop and prompt me.

### Subagent Scope

When dispatching work to subagents, the **orchestrating agent** retains responsibility for the rules above. Subagents should:

- Complete **only their assigned task** and return immediately — do not iterate to other tasks.
- **Not commit** changes — leave that to the orchestrator.
- **Not run linting or static analysis** unless explicitly asked — the orchestrator runs quality checks after the work is returned.
- Report any blockers or decisions made back to the orchestrator in the task result.

### Docker Deployment Architecture

**Do not run `docker compose` commands from this repo directory.** This dev path and the production deployment share the same Docker Compose project name (`podkeep`), so any `docker compose` command here affects production containers.

**NEVER run tests, artisan, pint, or phpstan inside the running production containers** (the `podkeep` compose group: `app`, `db`, `web`, `worker`):
- They serve live production traffic — running the test suite or mutating files inside `app` disrupts the running app.
- They do **not** reference this dev source directory (the bind mount is stale; source is baked into the image at build time), so they would test/build against the wrong code.
- Do not `docker compose cp` into them either.

**Production** lives at `/home/nate/Documents/docker/podkeep/`:
- Uses pre-built images (`podkeep-app:latest`, `podkeep-web:latest`) with source code baked into the image at build time.
- Only `storage/`, `database.sqlite`, and `.env` are volume-mounted. Source code and `public/build/` come from the image.
- The user rebuilds images manually — do not attempt it yourself.

**Development** lives at `/home/nate/src/podkeep/`:
- The `docker-compose.yml` uses `target: dev` which does NOT copy source into the image. The bind mount (`./src:/var/www/html`) is broken — the running container has stale files from its original creation, not current source.
- **Never run `npm run build` inside the running container** — it builds from stale files, not current source, and produces a Vite manifest that doesn't match the assets served by nginx.

### Running Tests, Static Analysis & Formatting (Ephemeral Containers)

The host has **no PHP, Node, or npm** installed. Run all PHP/JS tooling in **throwaway containers** started with `docker run --rm` against the pre-built `podkeep-app:latest` image, bind-mounting the dev source. Because of `--rm`, the container is discarded the moment the command exits — it never touches the running production containers.

The prefix is the same for every tool; only `--entrypoint` and the trailing arguments change. `--entrypoint` is overridden so the image's `docker-entrypoint.sh` (which runs production migrations against a DB that doesn't exist here) is skipped. Tests use SQLite `:memory:` (`phpunit.xml`), so **no database container is required**. The bind mount means host edits are visible immediately — no `cp` syncing.

**Tests (Pest):**
```bash
docker run --rm \
  -v /home/nate/src/podkeep/src:/var/www/html \
  -w /var/www/html \
  --entrypoint php \
  podkeep-app:latest \
  artisan test [test-file-paths...] [--filter=...]
```

**PHPStan (static analysis):**
```bash
docker run --rm \
  -v /home/nate/src/podkeep/src:/var/www/html \
  -w /var/www/html \
  --entrypoint vendor/bin/phpstan \
  podkeep-app:latest \
  analyse --no-progress
```

**Pint (formatting, apply to changed files):**
```bash
docker run --rm \
  -v /home/nate/src/podkeep/src:/var/www/html \
  -w /var/www/html \
  --entrypoint vendor/bin/pint \
  podkeep-app:latest \
  --dirty
```

**Refresh dependencies when `vendor/` is stale** (`/vendor` is gitignored; if you see `Class not found` / missing-binary errors, it is out of sync with `composer.json`):
```bash
docker run --rm \
  -v /home/nate/src/podkeep/src:/var/www/html \
  -w /var/www/html \
  --entrypoint composer \
  podkeep-app:latest \
  install --no-interaction --prefer-dist
```

**The correct workflow for any change:**
1. Edit files on the host.
2. Run tests / pint / phpstan via the ephemeral `docker run --rm` commands above (the bind mount picks up edits instantly).
3. When clean, commit the source changes.
4. For frontend changes specifically: the user rebuilds the Docker images and redeploys — the Dockerfile's `frontend` stage runs `npm run build` during image build, producing correct assets from current source. (Node/npm/fallow are not installed on the host; install them transiently inside an ephemeral container if a host-side frontend tool run is ever needed.)

<!-- MANUAL ADDITIONS END -->
