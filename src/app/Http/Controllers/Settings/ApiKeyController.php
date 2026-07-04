<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreApiKeyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ApiKeyController extends Controller
{
    /**
     * Show the user's API keys settings page.
     */
    public function index(): Response
    {
        $tokens = Auth::user()->tokens()
            ->select(['id', 'name', 'last_used_at', 'created_at', 'expires_at'])
            ->latest()
            ->get();

        return Inertia::render('settings/api-keys', [
            'tokens' => $tokens,
        ]);
    }

    /**
     * Create a new API key for the user.
     */
    public function store(StoreApiKeyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $token = $request->user()->createToken($validated['name']);

        return redirect()
            ->route('api-keys.index')
            ->with('success', 'API key created successfully.')
            ->with('new_token', $token->plainTextToken);
    }

    /**
     * Revoke the given API key.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        $token = $request->user()->tokens()->where('id', $id)->first();

        if (! $token) {
            return redirect()
                ->route('api-keys.index')
                ->with('error', 'API key not found.');
        }

        $token->delete();

        return redirect()
            ->route('api-keys.index')
            ->with('success', 'API key revoked.');
    }
}
