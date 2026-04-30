<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $owner = auth('web_owner')->user();

        return $owner
            && $owner->shop
            && $owner->shop->id === (int)$this->input('shop_id');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'exists:shops,id'],
            'item_category_id' => ['required', 'exists:item_categories,id'],
            'name' => ['required', 'string', 'max:50',
                Rule::unique('items', 'name')
                    ->where(fn ($query) => $query->where('shop_id', $this->shop_id))
            ],
            'information' => ['required', 'string', 'max:300'],
            'price_ex_tax' => ['required', 'integer', 'min:0'],
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
