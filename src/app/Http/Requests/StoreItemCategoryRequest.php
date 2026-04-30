<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemCategoryRequest extends FormRequest
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
            'sub_category_id' => ['required', 'integer', 'exists:sub_categories,id'],
            'name' => ['required', 'string', 'max:20',
                Rule::unique('item_categories')->where(
                    fn ($query) => $query->where('sub_category_id', $this->sub_category_id)
                )],
            'slug' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]+$/u',
                Rule::unique('item_categories')->where(
                    fn ($query) => $query->where('sub_category_id', $this->sub_category_id)
                )],
            ];
    }

    public function attributes()
    {
        return [
            'sub_category_id' => 'サブカテゴリー',
            'name' => '商品カテゴリー名',
            'slug' => '商品カテゴリースラグ'
        ];
    }
}
