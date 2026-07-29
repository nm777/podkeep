<?php

namespace App\Http\Requests\Api\V1;

use App\Models\LibraryItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AttachFeedItemRequest extends FormRequest
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
            'library_item_id' => [
                'required',
                'integer',
                Rule::unique('feed_items', 'library_item_id')->where('feed_id', $this->route('feed')),
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! LibraryItem::where('id', $value)->where('user_id', Auth::id())->exists()) {
                        $fail('The selected library item does not belong to you.');
                    }
                },
            ],
            'sequence' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
