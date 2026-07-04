# Contract: API Key Management (Web Routes)

**Feature**: 008-rest-api-keys

## Overview

API keys are managed through the web Settings UI (session-authenticated). These are NOT API endpoints — they require a session cookie + CSRF token. The issued keys are then used to authenticate stateless API calls.

## Middleware

All routes: `['auth', 'verified', 'approved']` (standard web middleware stack).

## Endpoints

### List API Keys

```
GET /settings/api-keys
```

Returns the Inertia settings page listing the user's API keys.

**Response**: Inertia page render with props:
```json
{
  "tokens": [
    {
      "id": 1,
      "name": "CI/CD uploads",
      "last_used_at": "2026-07-03T10:30:00.000000Z",
      "created_at": "2026-06-01T12:00:00.000000Z",
      "expires_at": null
    }
  ]
}
```

Note: The plaintext token value is NEVER included in the list response.

---

### Create API Key

```
POST /settings/api-keys
```

**Request** (form data with CSRF token):

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | Yes | max:255 |
| `_token` | string | Yes | CSRF token |

**Response**: Redirect to `/settings/api-keys` with flash data:

```json
{
  "flash": {
    "success": "API key created successfully.",
    "new_token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234"
  }
}
```

The `new_token` plaintext value is available in the flash session for exactly one page load. The page displays it with a copy button and a warning that it cannot be retrieved again.

**Backend behavior**:
- Calls `$user->createToken($name)` (Sanctum).
- Stores SHA-256 hash in `personal_access_tokens.token`.
- Returns plaintext `plainTextToken` in flash.

---

### Revoke API Key

```
DELETE /settings/api-keys/{id}
```

**Parameters**:

| Field | Type | Notes |
|-------|------|-------|
| `id` | int | The API key id |
| `_token` | string | CSRF token (header or body) |

**Response**: Redirect to `/settings/api-keys` with flash:

```json
{
  "flash": {
    "success": "API key revoked."
  }
}
```

**Authorization**: The key must belong to the authenticated user (`$user->tokens()->where('id', $id)`). Attempting to revoke another user's key returns 404 (no information leak).

**Effect**: All subsequent API requests using the revoked token return 401 immediately.

---

## Edge Cases

- **Duplicate key names**: Allowed. Users can have multiple keys with the same name.
- **Revoking current session's key**: Allowed. Does not affect the web session (web uses session auth, not token auth).
- **Key with last_used_at = null**: Never used since creation. Displayed as "Never used" in the UI.
