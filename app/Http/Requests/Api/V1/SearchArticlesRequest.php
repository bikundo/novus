<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SearchArticlesRequest extends FormRequest
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
            'q'         => ['nullable', 'string', 'max:255'],
            'source'    => ['nullable', 'string', 'exists:sources,slug'],
            'category'  => ['nullable', 'string', 'exists:categories,slug'],
            'page'      => ['nullable', 'integer', 'min:1'],
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
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
            'q.string'         => 'The search query must be a string.',
            'q.max'            => 'The search query must not exceed 255 characters.',
            'source.exists'    => 'The selected source does not exist.',
            'category.exists'  => 'The selected category does not exist.',
            'page.integer'     => 'The page number must be an integer.',
            'page.min'         => 'The page number must be at least 1.',
            'per_page.integer' => 'The per page value must be an integer.',
            'per_page.min'     => 'The per page value must be at least 1.',
            'per_page.max'     => 'The per page value must not exceed 100.',
        ];
    }
}
