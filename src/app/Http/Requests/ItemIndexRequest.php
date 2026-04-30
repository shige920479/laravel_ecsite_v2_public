<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $sortKeys = array_keys(config('constants.item_sort.options'));
        $this->merge(['item_search' => $this->item_search ? trim($this->item_search) : null]);
    
        return [
            'item_search' => ['nullable', 'string', 'max:' . config('constants.item_search.max_length')],
            'item_sort' => ['nullable', 'string', Rule::in($sortKeys)],
            'per_page' => ['nullable', 'integer', Rule::in(config('constants.pagination.per_page'))], 
            'page' => ['nullable', 'integer'],
        ];
    }
}
