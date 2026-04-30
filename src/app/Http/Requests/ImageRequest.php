<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImageRequest extends FormRequest
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
            'image' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024']
        ];

        if (request()->has('index')) {
            $rules['index'] = ['required', 'integer', 'between:1,4'];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'image' => '画像ファイル',
            'index' => '画像番号'
        ];
    }
}
