<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePreferenceRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'preferred_sources'      => ['nullable', 'array'],
            'preferred_sources.*'    => ['integer', 'exists:sources,id'],
            'preferred_categories'   => ['nullable', 'array'],
            'preferred_categories.*' => ['integer', 'exists:categories,id'],
            'preferred_authors'      => ['nullable', 'array'],
            'preferred_authors.*'    => ['integer', 'exists:authors,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'preferred_sources.array'        => 'The preferred sources must be an array.',
            'preferred_sources.*.integer'    => 'Each preferred source must be an integer.',
            'preferred_sources.*.exists'     => 'One or more selected sources do not exist.',
            'preferred_categories.array'     => 'The preferred categories must be an array.',
            'preferred_categories.*.integer' => 'Each preferred category must be an integer.',
            'preferred_categories.*.exists'  => 'One or more selected categories do not exist.',
            'preferred_authors.array'        => 'The preferred authors must be an array.',
            'preferred_authors.*.integer'    => 'Each preferred author must be an integer.',
            'preferred_authors.*.exists'     => 'One or more selected authors do not exist.',
        ];
    }
}
