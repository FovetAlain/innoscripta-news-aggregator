<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ArticleIndexRequest extends FormRequest
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
            'q' => ['sometimes', 'string', 'max:255'],
            'sources' => ['sometimes', 'array'],
            'sources.*' => ['integer'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['integer'],
            'authors' => ['sometimes', 'array'],
            'authors.*' => ['integer'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Accept both ?sources[]=1&sources[]=2 and the shorter ?sources=1,2 form.
     */
    protected function prepareForValidation(): void
    {
        foreach (['sources', 'categories', 'authors'] as $key) {
            $value = $this->input($key);

            if (is_string($value)) {
                $this->merge([$key => array_values(array_filter(explode(',', $value)))]);
            }
        }
    }
}
