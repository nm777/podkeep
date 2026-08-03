<?php

namespace App\Http\Resources;

use App\Enums\MediaType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read MediaType|null $media_type
 * @property-read bool $auto_generate_chapters
 */
class LibraryItemResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'source_type' => $this->source_type,
            'source_url' => $this->source_url,
            'media_type' => $this->media_type?->value,
            'auto_generate_chapters' => $this->auto_generate_chapters,
            'is_duplicate' => $this->is_duplicate,
            'duplicate_detected_at' => $this->duplicate_detected_at,
            'processing_status' => $this->processing_status?->value,
            'processing_status_display' => $this->processing_status_display,
            'processing_started_at' => $this->processing_started_at,
            'processing_completed_at' => $this->processing_completed_at,
            'processing_error' => $this->processing_error ? 'Media processing failed.' : null,
            'is_processing' => $this->isProcessing(),
            'is_pending' => $this->isPending(),
            'has_completed' => $this->hasCompleted(),
            'has_failed' => $this->hasFailed(),
            'media_file' => $this->when($this->mediaFile, MediaFileResource::make($this->mediaFile)),
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
