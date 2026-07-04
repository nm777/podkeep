<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'file' => ['required_without_all:source_url,url', 'prohibits:source_url,url', 'file', 'mimes:mp3,mp4,m4a,wav,ogg', 'max:512000'],
            'url' => ['required_without_all:source_url,file', 'prohibits:source_url,file', 'url', 'max:2048', 'regex:/\.(mp3|mp4|m4a|wav|ogg)(\?.*)?$/i'],
            'source_url' => ['required_without_all:file,url', 'prohibits:file,url', 'url', 'max:2048'],
            'feed_ids' => ['nullable', 'array'],
            'feed_ids.*' => ['integer', Rule::exists('feeds', 'id')->where('user_id', $this->user()?->id)],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
