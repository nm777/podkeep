<?php

use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/settings/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/profile');

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/profile');

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/settings/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('deleting an account retires its unshared media', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/account-deletion.mp3',
        'transcript' => [['start' => 0, 'end' => 1, 'text' => 'Transcript']],
    ]);
    LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);
    Storage::disk('public')->put($mediaFile->file_path, 'audio');

    $this->actingAs($user)
        ->delete('/settings/profile', ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    expect(MediaFile::find($mediaFile->id))->toBeNull();
    Storage::disk('public')->assertMissing($mediaFile->file_path);
});

test('deleting an account retains media shared with another user', function () {
    Storage::fake('public');
    $owner = User::factory()->create();
    $linkedUser = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'user_id' => $owner->id,
        'file_path' => 'media/shared-account-deletion.mp3',
    ]);
    LibraryItem::factory()->create([
        'user_id' => $owner->id,
        'media_file_id' => $mediaFile->id,
    ]);
    $linkedItem = LibraryItem::factory()->create([
        'user_id' => $linkedUser->id,
        'media_file_id' => $mediaFile->id,
    ]);
    Storage::disk('public')->put($mediaFile->file_path, 'audio');

    $this->actingAs($owner)
        ->delete('/settings/profile', ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    expect(MediaFile::find($mediaFile->id))->not->toBeNull();
    expect($linkedItem->refresh()->media_file_id)->toBe($mediaFile->id);
    Storage::disk('public')->assertExists($mediaFile->file_path);
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/settings/profile')
        ->delete('/settings/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect('/settings/profile');

    expect($user->fresh())->not->toBeNull();
});
