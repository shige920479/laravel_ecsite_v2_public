<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockCsvRequest extends FormRequest
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
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048']
        ];
    }

    public function messages()
    {
        return [
            'csv.required' => 'CSVファイルを選択してください。',
            'csv.file'     => 'ファイルの形式が正しくありません。',
            'csv.mimes'    => 'CSV形式のファイルをアップロードしてください。',
            'csv.max'      => 'CSVファイルのサイズが大きすぎます。',
        ];
    }
}
