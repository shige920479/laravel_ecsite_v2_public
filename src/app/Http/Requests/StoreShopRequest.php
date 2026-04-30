<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopRequest extends FormRequest
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
        $rules = [
            'name' => ['required', 'string', 'max:50', 'unique:shops,name'],
            'information' => ['required', 'string', 'max:1000'],
            'is_selling' => ['required', 'boolean'],
        ];

        if(! $this->session()->has('tmp_image') || empty(session('tmp_image'))) {
            $rules['image'] = ['required', 'file'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => 'Shop名',
            'information' => 'Shop情報',
            'is_selling' => 'ステータス',
        ];
    }

    public function messages()
    {
        return [
            'is_selling.boolean' => 'ステータスを正しく入力してください'
        ];
    }



}
