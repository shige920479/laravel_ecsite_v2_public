<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:20', 'unique:categories,name'],
            'slug' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]+$/u', 'unique:categories,slug']
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'カテゴリー名',
            'slug' => 'カテゴリースラグ'
        ];
    }
}
