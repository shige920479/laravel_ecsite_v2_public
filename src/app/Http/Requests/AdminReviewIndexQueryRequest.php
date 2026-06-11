<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminReviewIndexQueryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('web_admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search_word' => ['nullable', 'string', 'max:50'],
            'rating' => ['nullable', 'integer', 'between:1,5']
        ];
    }

    public function attributes(): array
    {
        return [
            'search_word' => '検索ワード',
            'rating' => '評価'
        ];
    }
}
