<?php

use App\Models\User;

it('rejects requests without authentication header', function () {
    $response = $this->getJson('/api/v1/me');

    $response->assertUnauthorized();
});

it('rejects requests with invalid token', function () {
    $response = $this->withHeader('Authorization', 'Bearer invalid-token-string')
        ->getJson('/api/v1/me');

    $response->assertUnauthorized();
});

it('accepts requests with valid sanctum token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/me');

    $response->assertOk();
});

it('rejects requests with revoked token', function () {
    $user = User::factory()->create();
    $newAccessToken = $user->createToken('test-token');
    $plainTextToken = $newAccessToken->plainTextToken;

    $newAccessToken->accessToken->delete();

    $response = $this->withHeader('Authorization', 'Bearer '.$plainTextToken)
        ->getJson('/api/v1/me');

    $response->assertUnauthorized();
});

it('rejects unverified users', function () {
    $user = User::factory()->unverified()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/me');

    $response->assertForbidden();
    $response->assertJson(['message' => 'Your email address is not verified.']);
});

it('rejects unapproved users', function () {
    $user = User::factory()->pending()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/me');

    $response->assertForbidden();
    $response->assertJson(['message' => 'Your account has not been approved.']);
});

it('updates last_used_at on token when used', function () {
    $user = User::factory()->create();
    $newAccessToken = $user->createToken('test-token');

    expect($newAccessToken->accessToken->last_used_at)->toBeNull();

    $this->withHeader('Authorization', 'Bearer '.$newAccessToken->plainTextToken)
        ->getJson('/api/v1/me');

    expect($newAccessToken->accessToken->fresh()->last_used_at)->not->toBeNull();
});
