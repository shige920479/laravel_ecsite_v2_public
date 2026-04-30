<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubCategoryRequest extends FormRequest
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
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:20',
                Rule::unique('sub_categories')->where(
                    fn ($query) => $query->where('category_id', $this->category_id)
                )],
            'slug' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]+$/u',
                Rule::unique('sub_categories')->where(
                    fn ($query) => $query->where('category_id', $this->category_id)
                )],
        ];
    }

    public function attributes()
    {
        return [
            'category_id' => 'カテゴリー',
            'name' => 'サブカテゴリー名',
            'slug' => 'サブカテゴリースラグ'
        ];
    }
}
