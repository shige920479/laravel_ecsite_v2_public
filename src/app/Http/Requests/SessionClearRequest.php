<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SessionClearRequest extends FormRequest
{
    private const ALLOWED_ROUTE = [
        'owner.shop.index',
        'owner.item.index',
        'owner.item.create',
        'owner.stocks.csv.create',
    ];
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
            'route' => ['required', 'string']
        ];
    }

    public function after(Validator $validator) 
    {
        return [
            function (Validator $validator) {
                if (! in_array($this->input('route'), self::ALLOWED_ROUTE)) {
                    abort(404);
                }
            }
        ];
    }
}
