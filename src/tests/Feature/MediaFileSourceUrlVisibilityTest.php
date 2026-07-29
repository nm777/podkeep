<?php

use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;

it('does not expose a shared media file source URL through the API', function () {
    $owner = User::factory()->create();
    $linkedUser = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'user_id' => $owner->id,
        'source_url' => 'https://owner.example.com/private.mp3',
    ]);
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $linkedUser->id,
        'media_file_id' => $mediaFile->id,
        'source_url' => 'https://linked-user.example.com/item.mp3',
    ]);

    $token = $linkedUser->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/library/'.$libraryItem->id)
        ->assertOk()
        ->assertJsonPath('data.source_url', 'https://linked-user.example.com/item.mp3')
        ->assertJsonMissingPath('data.media_file.source_url');
});

it('hides a shared media file source URL during model serialization', function () {
    $owner = User::factory()->create();
    $linkedUser = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'user_id' => $owner->id,
        'source_url' => 'https://owner.example.com/private.mp3',
    ]);
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $linkedUser->id,
        'media_file_id' => $mediaFile->id,
        'source_url' => 'https://linked-user.example.com/item.mp3',
    ])->load('mediaFile');

    $data = $libraryItem->toArray();

    expect($data['source_url'])->toBe('https://linked-user.example.com/item.mp3')
        ->and($data['media_file'])->not->toHaveKey('source_url');
});
