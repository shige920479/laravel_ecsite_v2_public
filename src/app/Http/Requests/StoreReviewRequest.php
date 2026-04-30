<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'star' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:50'],
            'review' => ['required', 'string', 'max:1000']
        ];
    }

    public function attributes(): array
    {
        return [
            'star' => '評価',
            'title' => 'タイトル',
            'review' => '投稿内容',
        ];
    }
}
