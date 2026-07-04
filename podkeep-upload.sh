#!/usr/bin/env bash
#
# Upload mp3 files to a podkeep feed via the web API.
#
# Resolves the feed by title (creating it only if it does not already exist),
# then uploads each file as a library item attached to that feed.
#
# Usage:
#   PODKEEP_EMAIL=you@example.com PODKEEP_PASSWORD=secret \
#       ./podkeep-upload.sh "The Two Towers" /home/nate/Music/The_Two_Towers/*.mp3
#
# Requires: curl, jq, grep
#
# Notes:
#   - Your podkeep account must have a verified email AND be admin-approved.
#     (Both are enforced on /feeds; the script verifies the session can reach it.)
#   - Uploads are throttled to 10/min server-side; this script paces requests
#     and also tolerates an occasional 429.
#
set -euo pipefail

BASE_URL="https://podkeep.app"
COOKIE_FILE="$(mktemp)"
BODY_FILE="$(mktemp)"
HEADER_FILE="$(mktemp)"
FEEDS_JSON="$(mktemp)"
trap 'rm -f "$COOKIE_FILE" "$BODY_FILE" "$HEADER_FILE" "$FEEDS_JSON"' EXIT

EMAIL="${PODKEEP_EMAIL:?Set PODKEEP_EMAIL env var}"
PASSWORD="${PODKEEP_PASSWORD:?Set PODKEEP_PASSWORD env var}"

# HTTP status code from the most recent request (populated by req()).
HTTP_CODE=""

die() { echo "ERROR: $*" >&2; exit 1; }

# Perform a curl request. Captures: status -> HTTP_CODE, body -> BODY_FILE,
# response headers -> HEADER_FILE. Cookie jar is shared across all calls.
req() {
    local code
    code=$(curl -sS -b "$COOKIE_FILE" -c "$COOKIE_FILE" -D "$HEADER_FILE" \
        -o "$BODY_FILE" -w "%{http_code}" "$@") || code="000"
    HTTP_CODE="$code"
}

# Extract the CSRF token from the <meta name="csrf-token"> tag in BODY_FILE.
csrf_from_body() { grep -oP 'csrf-token" content="\K[^"]+' "$BODY_FILE" || true; }

# Extract the feed ID from a Location: .../feeds/{id}/edit redirect header.
location_feed_id() { grep -oiP 'location:\s*\S*feeds/\K[0-9]+' "$HEADER_FILE" | head -n1 || true; }

# ─── 1. Authenticate ───────────────────────────────────────────
echo ">> Loading login page..."
req "$BASE_URL/login"
[ "$HTTP_CODE" = "200" ] || die "Could not load login page (HTTP $HTTP_CODE)."
CSRF="$(csrf_from_body)"
[ -n "$CSRF" ] || die "Could not find CSRF token on login page."

echo ">> Logging in as $EMAIL..."
# NOTE: /login returns a 302 redirect on BOTH success and failure, so the
# response itself cannot tell us whether authentication worked. We verify
# below by probing an auth-protected JSON endpoint.
req -X POST "$BASE_URL/login" \
    -H "X-CSRF-TOKEN: $CSRF" \
    -d "email=$EMAIL" \
    -d "password=$PASSWORD" \
    -d "_token=$CSRF"

# Probe GET /feeds with Accept: json. This endpoint sits behind the
# auth + verified + approved middleware, so a 200 confirms the whole stack.
echo ">> Verifying session..."
req "$BASE_URL/feeds" -H "Accept: application/json"
if [ "$HTTP_CODE" != "200" ]; then
    die "Login failed (GET /feeds returned HTTP $HTTP_CODE). Check that your credentials are correct, that your email is verified, and that an administrator has approved your account."
fi
cp "$BODY_FILE" "$FEEDS_JSON"
echo ">> Logged in."

# ─── 2. Resolve the feed (look up by title; create if missing) ──
FEED_TITLE="${1:?Usage: $0 \"Feed Title\" file1.mp3 [file2.mp3 ...]}"
shift

[ "$#" -gt 0 ] || die "No files given. Usage: $0 \"Feed Title\" file1.mp3 [file2.mp3 ...]"

FEED_ID="$(jq -r --arg title "$FEED_TITLE" \
    '[.[] | select(.title == $title)] | if length > 0 then .[0].id else empty end' \
    "$FEEDS_JSON" 2>/dev/null || true)"

if [ -n "$FEED_ID" ]; then
    echo ">> Found existing feed '$FEED_TITLE' (id $FEED_ID)."
else
    echo ">> Feed '$FEED_TITLE' not found; creating..."

    # Grab a CSRF token from the HTML dashboard (valid for the session).
    req "$BASE_URL/feeds"
    CSRF="$(csrf_from_body)"
    [ -n "$CSRF" ] || die "Could not get a CSRF token after login."

    # Accept: json makes validation failures return 422 JSON instead of a
    # 302 redirect, so we can distinguish them from the success redirect.
    req -X POST "$BASE_URL/feeds" \
        -H "X-CSRF-TOKEN: $CSRF" \
        -H "Accept: application/json" \
        -d "_token=$CSRF" \
        -d "title=$FEED_TITLE"

    case "$HTTP_CODE" in
        200|201|302)
            FEED_ID="$(location_feed_id)"
            [ -n "$FEED_ID" ] || die "Feed was created but the feed ID could not be parsed from the response. Open $BASE_URL/feeds to find it, then re-run (the feed now exists, so it will be reused)."
            echo ">> Created feed '$FEED_TITLE' (id $FEED_ID)."
            ;;
        422)
            die "Feed validation failed: $(jq -r '.errors | to_entries[] | "\(.key): \(.value|join(\", \"))"' "$BODY_FILE" 2>/dev/null || cat "$BODY_FILE")"
            ;;
        *)
            die "Failed to create feed (HTTP $HTTP_CODE): $(cat "$BODY_FILE")"
            ;;
    esac
fi

# ─── 3. Upload each file ───────────────────────────────────────
TOTAL="$#"
IDX=1
FAILURES=0

for FILE in "$@"; do
    if [ ! -f "$FILE" ]; then
        echo "   !! Skipping (not found): $FILE"
        FAILURES=$((FAILURES+1))
        IDX=$((IDX+1))
        continue
    fi

    EPISODE_TITLE="$(basename "${FILE%.*}")"
    echo ">> [$IDX/$TOTAL] Uploading: $FILE  (title: \"$EPISODE_TITLE\")"

    tries=0
    while :; do
        tries=$((tries+1))
        req -X POST "$BASE_URL/library" \
            -H "X-CSRF-TOKEN: $CSRF" \
            -H "Accept: application/json" \
            -F "_token=$CSRF" \
            -F "title=$EPISODE_TITLE" \
            -F "source_type=upload" \
            -F "feed_ids[]=$FEED_ID" \
            -F "file=@$FILE;type=audio/mpeg"

        case "$HTTP_CODE" in
            200|201|302)
                # The controller redirects on success; that 302 is our success signal.
                echo "   done."
                break
                ;;
            419)
                # CSRF token expired mid-session — refresh from the dashboard and retry.
                if [ "$tries" -lt 3 ]; then
                    echo "   CSRF expired — refreshing token and retrying..."
                    req "$BASE_URL/feeds"
                    CSRF="$(csrf_from_body)"
                    sleep 1
                    continue
                fi
                echo "   !! FAILED: CSRF token mismatch (gave up after $tries attempts)"
                FAILURES=$((FAILURES+1))
                break
                ;;
            422)
                echo "   !! FAILED: validation error:"
                jq -r '(.errors // {}) | to_entries[] | "      \(.key): \(.value|join(", "))"' "$BODY_FILE" 2>/dev/null \
                    || { echo "      $(cat "$BODY_FILE")"; }
                FAILURES=$((FAILURES+1))
                break
                ;;
            429)
                echo "   Rate limited — waiting 30s before retrying..."
                sleep 30
                continue
                ;;
            *)
                echo "   !! FAILED: unexpected HTTP $HTTP_CODE:"
                sed 's/^/      /' "$BODY_FILE" | head -n20
                FAILURES=$((FAILURES+1))
                break
                ;;
        esac
    done

    IDX=$((IDX+1))
    # Respect the server's 10 uploads/min throttle.
    sleep 7
done

echo ">> Done. $((TOTAL-FAILURES))/$TOTAL uploaded; processing happens async on the server."
[ "$FAILURES" -eq 0 ] || exit 1
