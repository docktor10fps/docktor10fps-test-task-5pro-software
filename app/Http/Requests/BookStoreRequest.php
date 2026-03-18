<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BookStoreRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'format' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:150',
            'isbn' => 'nullable|string|max:20',

            'edition' => 'nullable|integer|min:1',
            'pages' => 'nullable|integer|min:1',
            'published_date' => 'nullable|date',

            'publisher_id' => 'nullable|integer|exists:publishers,id',

            'author_ids' => 'nullable|array',
            'author_ids.*' => 'integer|exists:authors,id',

            'genre_ids' => 'nullable|array',
            'genre_ids.*' => 'integer|exists:genres,id',
        ];
    }
}
