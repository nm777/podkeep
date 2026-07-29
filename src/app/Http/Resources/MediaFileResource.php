<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/** @mixin \App\Models\MediaFile */
class MediaFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_url' => URL::temporarySignedRoute('files.show', now()->addHour(), ['file_path' => $this->file_path]),
            'file_hash' => $this->file_hash,
            'mime_type' => $this->mime_type,
            'filesize' => $this->filesize,
            'duration' => $this->duration,
            'source_url' => $this->when($request->user()?->id === $this->user_id, $this->source_url),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
