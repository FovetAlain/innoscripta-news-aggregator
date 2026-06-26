<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preferred_sources' => ['sometimes', 'array'],
            'preferred_sources.*' => ['integer', 'exists:sources,id'],
            'preferred_categories' => ['sometimes', 'array'],
            'preferred_categories.*' => ['integer', 'exists:categories,id'],
            'preferred_authors' => ['sometimes', 'array'],
            'preferred_authors.*' => ['integer', 'exists:authors,id'],
        ];
    }
}
