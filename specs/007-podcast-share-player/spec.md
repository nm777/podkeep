# Feature Spec: Podcast Share Player Page

**Branch**: `007-podcast-share-player` | **Date**: 2026-05-25

## Problem

Users can currently share podcasts only via RSS feed URLs, which require recipients to paste the URL into a podcast app. There is no way to share a podcast as a web page where someone can browse episodes and listen directly in the browser. This limits sharing to users who know how to add RSS feeds to podcast apps.

## Proposed Solution

Add a public-facing web player page at `/share/{user_guid}/{feed_slug}` that:

1. Displays feed metadata (title, description, cover image)
2. Lists all feed items (episodes) with titles, descriptions, and published dates
3. Provides an inline audio player for playing episodes directly in the browser
4. Respects existing access control: public feeds are open; private feeds require a `?token=` query parameter
5. Includes a link to copy the RSS feed URL for subscribing in a podcast app

## User Stories

- As a feed owner, I want to share a web link to my podcast so recipients can listen in their browser without needing a podcast app
- As a feed owner, I want private feeds to require a token for web access, consistent with how RSS and media access already works
- As a recipient, I want to see a list of all episodes with the ability to play any of them
- As a recipient, I want to copy the RSS URL to subscribe in my podcast app if I choose
- As a feed owner, I want a "Copy Share Link" button alongside the existing "Copy RSS URL" button in the feed card

## Requirements

### Functional

- New route `GET /share/{user_guid}/{feed_slug}` accessible without authentication
- Public feeds (`is_public = true`): accessible without token
- Private feeds (`is_public = false`): require `?token={feed_token}` matching the feed's stored token
- Invalid/expired token returns 404 (same behavior as RssController)
- Page displays feed title, description, cover image
- Page lists all feed items ordered by sequence
- Each item shows title, description, published date, duration
- Audio player supports play/pause, seeking, volume control via native HTML5 `<audio>` element
- Media URLs for private feeds include `?feed_token={token}` query parameter (existing MediaController pattern)
- "Copy RSS URL" button on the share page
- "Copy Share Link" button added to existing feed card component

### Non-Functional

- Rate limited to 120 requests/minute (same as RSS route)
- Page renders via Inertia.js (SSR-compatible)
- Responsive design (mobile-first)
- Dark mode support (consistent with existing app)
- No new database tables or migrations required
- Must not expose feed tokens to unauthorized users

### Out of Scope

- User authentication/registration from share page
- Download of media files from share page
- Playlist/queue management
- Playback progress tracking
- Episode show notes beyond description
- Comments or social features
