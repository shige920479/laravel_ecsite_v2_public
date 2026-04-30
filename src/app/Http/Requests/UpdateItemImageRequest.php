<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemImageRequest extends FormRequest
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
            'item_image_ids.*' => ['nullable', 'integer', 'exists:item_images,id'],
            'filenames.*' => ['nullable', 'string'],
            'sort_order.*' => ['nullable', 'between:1,4'], 
            'def_sort.*' => ['nullable', 'between:1,4'], 
        ];
    }

    public function attributes()
    {
        return [
            'filename.*' => '商品画像ファイル',
            'sort_order.*' => '並び順',
            'def_sort.*' => '変更前の並び順'
        ];
    }
}
