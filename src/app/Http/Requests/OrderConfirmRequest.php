<?php

namespace App\Http\Requests;

use App\Models\Cart;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class OrderConfirmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $ids = $this->input('ids', []);
        return Cart::query()
            ->whereIn('id', $ids)
            ->where('user_id', Auth::id())
            ->count() === count($ids);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:carts,id']
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'カートを指定してください',
            'ids.*.exists' => '指定したカートが存在しません',
        ];
    }
}
