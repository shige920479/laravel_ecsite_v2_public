<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopRequest extends FormRequest
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
        $shopId = $this->route('shop')->id;
        return [
            'name' => ['required', 'string', 'max:50',
                Rule::unique('shops', 'name')->ignore($shopId)
            ],
            'information' => ['required', 'string', 'max:1000'],
            'is_selling' => ['required', 'boolean']
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Shop名',
            'information' => 'Shop情報',
            'is_selling' => 'ステータス',
        ];
    }

    public function  messages()
    {
        return [
            'is_selling.boolean' => 'ステータスを正しく入力してください'
        ];
    }
}
