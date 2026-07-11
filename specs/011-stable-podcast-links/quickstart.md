# Quickstart: Stable Podcast Links

**Feature**: 011-stable-podcast-links

## The Change (summary)

Remove the slug regeneration from the web feed update. One line in
`src/app/Http/Controllers/FeedController.php::update()` (currently line 86):

```php
'slug' => $this->generateUniqueSlug($validated['title'], $feed->id),
```

is deleted. The slug stays as-is; `title` still updates. Then remove the now-dead
`$excludeFeedId` parameter from the web controller's private
`generateUniqueSlug()` (its only remaining caller, `store()`, does not pass it).

No migration. No frontend change. No API change.

## Verification Workflow

> **Note (from AGENTS.md):** PHP/artisan/phpstan run inside the production Docker
> container. Source is **not** bind-mounted, so changed files must be synced into
> the container before running PHP tooling. The compose project lives at
> `/home/nate/Documents/docker/podkeep/`. Do **not** run `docker compose` from the
> dev repo directory (shared project name with production).

### 1. Write the failing test first (TDD — red)

Create `src/tests/Feature/StableFeedLinksTest.php` (Pest). It should assert, at
minimum:

- Renaming a feed via the web `PUT /feeds/{id}` does **not** change the `slug`,
  while the `title` does update.
- Editing only non-title fields (description / `is_public`) does not change the
  `slug`.
- After a rename, the **original** RSS URL (`/rss/{user_guid}/{original_slug}`)
  still returns `200` XML and the body contains the **new** title.
- After a rename, the **original** share URL (`/share/{user_guid}/{original_slug}`)
  still returns `200` and shows the new title.
- Renaming via the API (`PUT /api/v1/feeds/{id}`) does not change the `slug`.
- Multiple sequential renames leave the original `slug` intact.
- A non-owner `PUT /feeds/{id}` is still `403` (regression guard for FR-006).

Model the test on `src/tests/Feature/FeedEditTest.php` (Pest style, factories,
`actingAs`).

### 2. Sync the new test into the container and run it — expect failure

```bash
# from the production compose directory
docker compose cp src/tests/Feature/StableFeedLinksTest.php app:/var/www/html/tests/Feature/StableFeedLinksTest.php
docker compose exec app php artisan test tests/Feature/StableFeedLinksTest.php
```

Expected: the slug-stability assertions fail (the current code overwrites the
slug).

### 3. Apply the fix (green)

Edit `src/app/Http/Controllers/FeedController.php`:

- Delete the `'slug' => ...` line in `update()`.
- Simplify `generateUniqueSlug()` (drop the unused `$excludeFeedId` parameter and
  its branches).

### 4. Sync the controller change and re-run the new test

```bash
docker compose cp src/app/Http/Controllers/FeedController.php app:/var/www/html/app/Http/Controllers/FeedController.php
docker compose exec app php artisan test tests/Feature/StableFeedLinksTest.php
```

Expected: all green.

### 5. Regression — run the related existing suites

```bash
docker compose exec app php artisan test tests/Feature/FeedEditTest.php tests/Feature/FeedManagementTest.php tests/Feature/ShareControllerTest.php tests/Feature/RssFeedTest.php
```

Expected: all green. In particular, `FeedEditTest`'s "allows feed owner to update
feed details" still passes (title still updates; only slug stability is new).

### 6. Static analysis

```bash
docker compose exec app ./vendor/bin/phpstan
```

Expected: clean.

### 7. Commit

Once green and clean, commit the controller change plus the new test file on the
`011-stable-podcast-links` branch. (Per AGENTS.md, do not run `npm run build`
inside the container — there is no frontend change anyway.)
