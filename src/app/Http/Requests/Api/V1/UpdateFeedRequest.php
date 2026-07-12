<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\FeedType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeedRequest extends FormRequest
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
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'website_url' => ['sometimes', 'nullable', 'string', 'url', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
            'feed_type' => ['sometimes', 'nullable', 'string', Rule::enum(FeedType::class)],
        ];
    }
}
