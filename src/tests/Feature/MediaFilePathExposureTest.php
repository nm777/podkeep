<?php

use App\Http\Resources\LibraryItemResource;
use App\Http\Resources\MediaFileResource;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('media file resource includes public_url but not file_path', function () {
    $mediaFile = MediaFile::factory()->create();

    $resource = new MediaFileResource($mediaFile);
    $data = $resource->resolve();

    expect($data)->not->toHaveKey('file_path');
    expect($data)->toHaveKey('public_url');
    expect($data['public_url'])->toContain('/files/'.$mediaFile->file_path)
        ->toContain('expires=')
        ->toContain('signature=');
});

it('library item resource does not leak file_path through nested media_file', function () {
    $mediaFile = MediaFile::factory()->create();
    $item = LibraryItem::factory()->create([
        'media_file_id' => $mediaFile->id,
    ]);

    $resource = new LibraryItemResource($item);
    $item->load('mediaFile');
    $data = (new LibraryItemResource($item))->resolve();

    expect($data)->toHaveKey('media_file');
    $mediaFileData = $data['media_file']->resolve();
    expect($mediaFileData)->not->toHaveKey('file_path');
    expect($mediaFileData)->toHaveKey('public_url');
});

it('stores media outside the public storage symlink and serves it through files', function () {
    Storage::fake('media');

    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/private.mp3',
    ]);
    Storage::disk('media')->put($mediaFile->file_path, 'audio');

    expect(config('filesystems.disks.media.root'))->not->toBe(config('filesystems.links.'.public_path('storage')))
        ->and(config('filesystems.disks.media'))->not->toHaveKey('url');

    $this->actingAs($user)
        ->get('/files/'.$mediaFile->file_path)
        ->assertSuccessful();

    $this->get('/storage/'.$mediaFile->file_path)->assertClientError();
});

it('allows an admin to access any media file', function () {
    Storage::fake('media');

    $mediaFile = MediaFile::factory()->create(['file_path' => 'media/private.mp3']);
    Storage::disk('media')->put($mediaFile->file_path, 'audio');

    $this->actingAs(User::factory()->admin()->create())
        ->get('/files/'.$mediaFile->file_path)
        ->assertSuccessful();
});
