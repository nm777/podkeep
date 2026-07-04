<?php

use App\Models\User;

describe('API key creation', function () {
    it('creates an api key and shows plaintext token once', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/settings/api-keys', [
            'name' => 'My API Key',
        ]);

        $response->assertRedirect('/settings/api-keys');
        $response->assertSessionHas('new_token');
        expect(session('new_token'))->toBeString()->not->toBeEmpty();

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'My API Key',
        ]);
    });

    it('validates name is required', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/settings/api-keys', []);

        $response->assertSessionHasErrors('name');
    });

    it('prevents unapproved users from creating keys', function () {
        $user = User::factory()->pending()->create();

        $response = $this->actingAs($user)->post('/settings/api-keys', [
            'name' => 'Should Not Be Created',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('personal_access_tokens', [
            'name' => 'Should Not Be Created',
        ]);
    });
});

describe('API key listing', function () {
    it('lists api keys without plaintext token', function () {
        $user = User::factory()->create();
        $user->createToken('Listable Token');

        $response = $this->actingAs($user)->get('/settings/api-keys');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('tokens', 1)
            ->has('tokens.0.name')
            ->has('tokens.0.created_at')
            ->has('tokens.0.last_used_at')
            ->missing('tokens.0.token')
        );
    });

    it('only shows the authenticated users keys', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $user->createToken('My Token');
        $otherUser->createToken('Someone Elses Token');

        $response = $this->actingAs($user)->get('/settings/api-keys');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('tokens', 1)
            ->where('tokens.0.name', 'My Token')
        );
    });
});

describe('API key revocation', function () {
    it('revokes an api key', function () {
        $user = User::factory()->create();
        $token = $user->createToken('Revocable Token');

        $response = $this->actingAs($user)
            ->delete('/settings/api-keys/'.$token->accessToken->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    });

    it('prevents revoking another users key', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $token = $otherUser->createToken('Someone Elses Token');

        $response = $this->actingAs($user)
            ->delete('/settings/api-keys/'.$token->accessToken->id);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->accessToken->id,
            'name' => 'Someone Elses Token',
        ]);
    });

    it('revoked token immediately fails authentication', function () {
        $user = User::factory()->create();
        $plainTextToken = $user->createToken('Test Token')->plainTextToken;

        $user->tokens()->delete();

        $response = $this->withHeader('Authorization', 'Bearer '.$plainTextToken)
            ->getJson('/api/v1/me');

        $response->assertUnauthorized();
    });
});
