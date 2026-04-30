<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
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
            'nickname' => ['nullable', 'string', 'max:30'],
            'postcode' => ['required', 'string', 'regex:/^\d{3}-?\d{4}$/'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\d{2,4}-?\d{2,4}-?\d{3,4}$/'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'postcode' => str_replace('-', '', $this->postcode),
            'address' => trim($this->address),
            'phone' => str_replace('-', '', $this->phone)
        ]);
    }

    public function attributes(): array
    {
        return [
            'nickname' => 'ニックネーム',
            'postcode' => '郵便番号'
        ];
    }

    public function messages(): array
    {
        return [
            'postcode.regex' => '郵便番号は7桁で入力してください',
            'phone.regex' => '電話番号は10〜11桁の数字で入力してください',
        ];
    }
}
