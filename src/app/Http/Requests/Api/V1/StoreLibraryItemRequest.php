<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\MediaType;
use App\Services\YouTubeUrlValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLibraryItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'source_type' => ['sometimes', 'in:upload,url,youtube'],
            'media_type' => ['nullable', 'string', Rule::enum(MediaType::class)],
            'auto_generate_chapters' => ['nullable', 'boolean'],
            'file' => ['required_without_all:source_url,url', 'prohibits:source_url,url', 'file', 'mimes:mp3,mp4,m4a,wav,ogg,webm,mkv,mov,avi', 'max:'.(config('constants.media.max_bytes') / 1024)],
            'url' => ['required_without_all:source_url,file', 'prohibits:source_url,file', 'url', 'max:2048', 'regex:/\.(mp3|mp4|m4a|wav|ogg|webm|mkv|mov|avi)(\?.*)?$/i'],
            'source_url' => ['required_without_all:file,url', 'prohibits:file,url', 'url', 'max:2048'],
            'feed_ids' => ['nullable', 'array'],
            'feed_ids.*' => ['integer', Rule::exists('feeds', 'id')->where('user_id', $this->user()?->id)],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('source_type') !== 'youtube') {
                return;
            }

            $urlField = $this->filled('source_url') ? 'source_url' : 'url';
            $sourceUrl = $this->input($urlField);

            if (is_string($sourceUrl) && ! YouTubeUrlValidator::isValidYouTubeUrl($sourceUrl)) {
                $validator->errors()->add($urlField, 'Invalid YouTube URL');
            }
        }];
    }
}
