<?php

namespace App\Http\Requests;

use App\Models\Stock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreStockRequest extends FormRequest
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

        return [
            'stock_diff' => ['required', 'integer', 'min:1'],
            'up_down' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:255']
        ];
    }

    public function after(): array
    {
        return [
            function(Validator $validator) {
                $quantity = (int)$this->input('stock_diff') * ($this->boolean('up_down') ? 1 : -1);
                $totalStock = $this->route('item')->stock_current;
                if(($totalStock + $quantity) < 0) {
                    $validator->errors()->add(
                        'stock_diff',
                        "在庫数を超えています（在庫：{$totalStock}個）"
                    );
                }
            }
        ];
    }

    public function attributes()
    {
        return [
            'item_id' => "商品",
            'stock_diff' => "数量の入力",
            'up_down' => '増やす/減らす ',
            'reason' => "増減理由/備考",
        ];
    }

    public function messages()
    {
        return [
            'up_down.boolean' => '増やす/減らすは正しく入力願います'
        ];
    }
}
