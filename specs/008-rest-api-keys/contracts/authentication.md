# Contract: Authentication

**Feature**: 008-rest-api-keys
**Base URL**: `/api/v1`

## Overview

All API endpoints require authentication via a personal API key (bearer token). Tokens are created and revoked through the web Settings UI (session-authenticated). API requests are stateless.

## Authentication Header

Every API request must include an `Authorization` header with a bearer token:

```
Authorization: Bearer {plaintext_token}
```

The plaintext token is returned exactly once when the key is created via `POST /settings/api-keys` (web route). Format: `{id}|{40-char-hash}` (e.g., `1|abc123def456...`).

## Middleware Stack

All `/api/v1/*` routes use:

| Middleware | Purpose | Failure Response |
|------------|---------|-----------------|
| `throttle:api` | Rate limiting (60 req/min per user) | 429 Too Many Requests |
| `auth:sanctum` | Validate bearer token, load user | 401 Unauthorized |
| `verified.api` | Require verified email | 403 Forbidden |
| `approved.api` | Require approved account | 403 Forbidden |

## Error Responses

### Missing or Invalid Token (401)

```json
{
  "message": "Unauthenticated."
}
```

Triggered by: missing `Authorization` header, malformed bearer token, revoked token, or token hash mismatch.

### Unverified Email (403)

```json
{
  "message": "Your email address is not verified."
}
```

### Unapproved Account (403)

```json
{
  "message": "Your account has not been approved."
}
```

### Rate Limit Exceeded (429)

```json
{
  "message": "Too Many Attempts."
}
```

Headers: `Retry-After`, `X-RateLimit-Limit`, `X-RateLimit-Remaining`.

### Validation Error (422)

```json
{
  "message": "The title field is required.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

### Not Found (404)

```json
{
  "message": "Record not found."
}
```

Returned when a resource does not exist or belongs to another user (no information leak about other users' resources).

### Forbidden (403)

```json
{
  "message": "This action is unauthorized."
}
```

Returned when attempting to modify/delete a resource owned by another user.
