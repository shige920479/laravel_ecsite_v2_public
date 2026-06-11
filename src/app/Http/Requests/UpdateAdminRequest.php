<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateAdminRequest extends FormRequest
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
        $id = $this->route('admin')->id;
        $roles = Role::pluck('name')->values()->all();
        return [
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('admins', 'email')->ignore($id)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::in($roles)],
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
