<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\EpisodeOrderType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedRequest extends FormRequest
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
            'website_url' => ['nullable', 'string', 'url', 'max:255'],
            'is_public' => ['boolean'],
            'episode_order' => ['nullable', 'string', Rule::enum(EpisodeOrderType::class)],
        ];
    }
}
