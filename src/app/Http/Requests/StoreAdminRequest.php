<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('web_admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roles = Role::pluck('name')->values()->all();
        return [
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:50', 
                Rule::unique('admins')->where(
                    fn ($query) => $query->whereNull('deleted_at')
                )
            ],
            'password' => ['required', 'string', 'confirmed', 'regex:/\A(?=.*?[A-z])(?=.*?\d)[A-z\d]{8,12}+\z/'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', Rule::in($roles)]
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '管理者名',
            'email' => 'メールアドレス',
            'roles' => '権限'
        ];
    }

    public function messages(): array
    {
        return [
            'roles.*.in' => '不正な権限が入力されました'
        ];
    }
}
