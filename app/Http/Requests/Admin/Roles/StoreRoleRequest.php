<?php

namespace App\Http\Requests\Admin\Roles;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:roles,slug'],
            'permissions' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên role không được để trống',
            'slug.unique' => 'Slug đã tồn tại',
        ];
    }
}
