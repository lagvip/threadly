<?php

namespace App\Http\Requests\Admin\Colors;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->name),
            'code' => strtoupper(trim((string) $this->code)),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('colors', 'name')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('colors', 'code')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên màu không được để trống.',
            'name.max' => 'Tên màu tối đa 255 ký tự.',
            'name.unique' => 'Tên màu này đã tồn tại.',
            'code.required' => 'Mã màu không được để trống.',
            'code.max' => 'Mã màu tối đa 255 ký tự.',
            'code.unique' => 'Mã màu này đã tồn tại.',
        ];
    }
}
