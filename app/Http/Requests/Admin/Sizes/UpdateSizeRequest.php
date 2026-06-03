<?php

namespace App\Http\Requests\Admin\Sizes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->name)]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'regex:/^[0-9]+$/',
                'max:255',
                Rule::unique('sizes', 'name')
                    ->ignore($this->route('id'))
                    ->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên size không được để trống',
            'name.regex' => 'Size chỉ được nhập số',
            'name.max' => 'Tên size tối đa 255 ký tự',
            'name.unique' => 'Size này đã tồn tại',
        ];
    }
}
