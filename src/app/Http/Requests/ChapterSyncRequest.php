<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChapterSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string|\Closure>
     */
    public function rules(): array
    {
        $duration = $this->route('library_item')?->mediaFile?->duration;

        return [
            'chapters' => ['nullable', 'array', 'max:20', function (string $attribute, mixed $value, \Closure $fail) {
                $starts = is_array($value) ? array_column($value, 'start_time') : [];
                if (count($starts) !== count(array_unique($starts))) {
                    $fail('Each chapter must have a unique start time.');
                }
            }],
            'chapters.*.start_time' => ['required', 'integer', 'min:0', function (string $attribute, mixed $value, \Closure $fail) use ($duration) {
                if ($duration !== null && $value >= $duration) {
                    $fail('The start time must be before the end of the media.');
                }
            }],
            'chapters.*.title' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }
}
