# Quickstart: REST API with API Key Authentication

**Feature**: 008-rest-api-keys

## Prerequisites

- A podkeep account that is email-verified and admin-approved
- Access to the podkeep web UI (to create an API key)

## Step 1: Create an API Key

1. Log in to podkeep at your deployment URL
2. Navigate to **Settings > API Keys**
3. Click **Create API Key**, enter a name (e.g., "CLI uploads")
4. **Copy the generated key immediately** — it is shown only once
   - Format: `{id}|{40-char-hash}` (e.g., `1|abc123def456ghi789jkl012mno345pqr678stu901`)

Save the key to an environment variable:

```bash
export PODKEEP_API_KEY="1|abc123def456ghi789jkl012mno345pqr678stu901"
```

## Step 2: Create a Podcast Feed

```bash
curl -s -X POST https://podkeep.app/api/v1/feeds \
  -H "Authorization: Bearer $PODKEEP_API_KEY" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "My API Podcast",
    "description": "Created via the REST API",
    "is_public": false
  }'
```

**Response** (201):
```json
{
  "data": {
    "id": 1,
    "title": "My API Podcast",
    "slug": "my-api-podcast",
    "user_guid": "550e8400-...",
    "token": "a1b2c3d4...",
    "..."
  }
}
```

Save the feed ID:
```bash
FEED_ID=1
```

## Step 3: List Your Feeds

```bash
curl -s https://podkeep.app/api/v1/feeds \
  -H "Authorization: Bearer $PODKEEP_API_KEY" \
  -H "Accept: application/json"
```

## Step 4: Upload an MP3 File

```bash
curl -s -X POST https://podkeep.app/api/v1/library \
  -H "Authorization: Bearer $PODKEEP_API_KEY" \
  -H "Accept: application/json" \
  -F "title=Episode 1 - Getting Started" \
  -F "description=Our first episode" \
  -F "feed_ids[]=$FEED_ID" \
  -F "file=@/path/to/episode.mp3;type=audio/mpeg"
```

**Response** (201):
```json
{
  "data": {
    "id": 10,
    "title": "Episode 1 - Getting Started",
    "processing_status": "pending",
    "..."
  },
  "message": "File uploaded. Processing will complete shortly."
}
```

Save the library item ID:
```bash
ITEM_ID=10
```

## Step 5: Poll Processing Status

Media files are processed asynchronously. Poll until `processing_status` is `completed`:

```bash
curl -s https://podkeep.app/api/v1/library/$ITEM_ID \
  -H "Authorization: Bearer $PODKEEP_API_KEY" \
  -H "Accept: application/json" | jq '.data.processing_status'
```

```bash
# Simple poll loop (checks every 5 seconds)
while true; do
  STATUS=$(curl -s https://podkeep.app/api/v1/library/$ITEM_ID \
    -H "Authorization: Bearer $PODKEEP_API_KEY" \
    -H "Accept: application/json" | jq -r '.data.processing_status')

  echo "Status: $STATUS"
  [ "$STATUS" = "completed" ] && break
  [ "$STATUS" = "failed" ] && { echo "Processing failed!"; break; }
  sleep 5
done
```

## Step 6: Attach to a Feed (if not done at upload time)

If you did not pass `feed_ids[]` during upload, attach the item to a feed separately:

```bash
curl -s -X POST https://podkeep.app/api/v1/feeds/$FEED_ID/items \
  -H "Authorization: Bearer $PODKEEP_API_KEY" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"library_item_id\": $ITEM_ID}"
```

## Step 7: Verify via RSS

Once processing completes and the item is attached, verify it appears in the RSS feed:

```bash
# The RSS URL uses the feed's user_guid and slug
curl -s "https://podkeep.app/rss/550e8400-.../my-api-podcast"
```

## Step 8: Add Media via URL (Alternative to Upload)

Instead of uploading a file, you can provide a direct URL to an audio/video file:

```bash
curl -s -X POST https://podkeep.app/api/v1/library \
  -H "Authorization: Bearer $PODKEEP_API_KEY" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{
    \"title\": \"Episode 2 - From URL\",
    \"url\": \"https://example.com/media/episode2.mp3\",
    \"feed_ids\": [$FEED_ID]
  }"
```

## Step 9: Reorder Episodes in a Feed

```bash
curl -s -X PUT https://podkeep.app/api/v1/feeds/$FEED_ID/items/reorder \
  -H "Authorization: Bearer $PODKEEP_API_KEY" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "items": [
      {"id": 2, "sequence": 0},
      {"id": 1, "sequence": 1}
    ]
  }'
```

## Step 10: Retry a Failed Upload

If an item fails processing:

```bash
curl -s -X POST https://podkeep.app/api/v1/library/$ITEM_ID/retry \
  -H "Authorization: Bearer $PODKEEP_API_KEY" \
  -H "Accept: application/json"
```

## Full Workflow Script (Bash)

```bash
#!/usr/bin/env bash
set -euo pipefail

API_KEY="${PODKEEP_API_KEY:?Set PODKEEP_API_KEY}"
BASE="https://podkeep.app/api/v1"
AUTH="Authorization: Bearer $API_KEY"
JSON="Accept: application/json"

# 1. Create feed
FEED=$(curl -s -X POST "$BASE/feeds" \
  -H "$AUTH" -H "$JSON" -H "Content-Type: application/json" \
  -d '{"title":"Script Podcast"}')
FEED_ID=$(echo "$FEED" | jq '.data.id')

# 2. Upload episode
ITEM=$(curl -s -X POST "$BASE/library" \
  -H "$AUTH" -H "$JSON" \
  -F "title=Episode 1" \
  -F "feed_ids[]=$FEED_ID" \
  -F "file=@./episode.mp3;type=audio/mpeg")
ITEM_ID=$(echo "$ITEM" | jq '.data.id')

# 3. Poll until complete
while true; do
  STATUS=$(curl -s "$BASE/library/$ITEM_ID" -H "$AUTH" -H "$JSON" | jq -r '.data.processing_status')
  echo "Processing: $STATUS"
  [ "$STATUS" = "completed" ] && break
  [ "$STATUS" = "failed" ] && { echo "FAILED"; exit 1; }
  sleep 5
done

echo "Done! Feed ID: $FEED_ID, Item ID: $ITEM_ID"
```

## Revoke an API Key

When a key is no longer needed, revoke it from the UI:

1. Navigate to **Settings > API Keys**
2. Click **Revoke** next to the key
3. All API requests using that key immediately return 401

## Error Reference

| Status | Meaning |
|--------|---------|
| 401 | Missing, invalid, or revoked API key |
| 403 | Email not verified, account not approved, or not your resource |
| 404 | Resource not found (or belongs to another user) |
| 422 | Validation error (check `errors` field for details) |
| 429 | Rate limit exceeded (60 req/min) — see `Retry-After` header |
