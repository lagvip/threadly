<?php

namespace App\Http\Requests\Admin\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique('categories', 'name')],
            'image' => ['required', 'image', 'max:2048'],
            'id_parent' => ['nullable', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Bạn chưa nhập tên.',
            'name.unique' => 'Tên này đã tồn tại, vui lòng chọn tên khác.',
            'image.required' => 'Bạn chưa chọn ảnh.',
            'image.image' => 'File phải là ảnh hợp lệ.',
            'image.max' => 'Ảnh không được vượt quá 2MB.',
            'id_parent.exists' => 'Danh mục cha không tồn tại.',
        ];
    }
}
