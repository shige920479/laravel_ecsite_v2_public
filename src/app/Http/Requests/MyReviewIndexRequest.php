<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MyReviewIndexRequest extends FormRequest
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
            'review_sort' => ['required', 'string', 'in:desc,asc'],
            'per_page' => ['required', 'integer', 'max:100'],
            'page' => ['required', 'integer'],
        ];
    }
}
