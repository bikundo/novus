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
            'preferred_sources'      => ['nullable', 'array', 'max:50'],
            'preferred_sources.*'    => ['string'],
            'preferred_categories'   => ['nullable', 'array', 'max:50'],
            'preferred_categories.*' => ['string'],
            'preferred_authors'      => ['nullable', 'array', 'max:50'],
            'preferred_authors.*'    => ['string'],
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
            'preferred_sources.max'          => 'You cannot select more than 50 preferred sources.',
            'preferred_sources.*.string'     => 'Each preferred source must be a string.',
            'preferred_categories.array'     => 'The preferred categories must be an array.',
            'preferred_categories.max'       => 'You cannot select more than 50 preferred categories.',
            'preferred_categories.*.string'  => 'Each preferred category must be a string.',
            'preferred_authors.array'        => 'The preferred authors must be an array.',
            'preferred_authors.max'          => 'You cannot select more than 50 preferred authors.',
            'preferred_authors.*.string'     => 'Each preferred author must be a string.',
        ];
    }
}
