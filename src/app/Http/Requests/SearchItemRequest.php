<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchItemRequest extends FormRequest
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
        $sortKeys = array_keys(config('constants.owner_item_sort.options'));

        return [
            'search' => ['nullable', 'string', 'max:'.config('constants.item_search.max_length')],
            'sort' => ['nullable', 'string', Rule::in($sortKeys)],
        ];
    }

    public function attributes(): array
    {
        return [
            'search' => '検索ワード',
            'sort' => 'ソート条件',
        ];
    }
}
