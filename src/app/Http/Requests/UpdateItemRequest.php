<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('web_owner')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $item = $this->route('item');

        return [
            'shop_id' => ['required', 'exists:shops,id'],
            'item_category_id' => ['required', 'exists:item_categories,id'],
            'name' => ['required', 'string', 'max:50',
                Rule::unique('items', 'name')
                    ->where('shop_id', $item->shop_id)->ignore($item->id)
            ],
            'price_ex_tax' => ['required', 'integer', 'min:1'],
            'information' => ['required', 'string', 'max:300'],
            'is_selling' => ['required', 'boolean'],
        ];
    }
    
    public function attributes()
    {
        return [
            'shop_id' => 'ショップ',
            'item_category_id' => 'カテゴリー',
            'name' => '商品名',
            'information' => '商品情報',
            'price_ex_tax' => '金額',
            'is_selling' => 'ステータス',
        ];
    }
}
