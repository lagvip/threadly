<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:250'],
            'image' => ['required', 'image', 'max:2048'],
            'link' => ['nullable', 'string', 'max:500'],
            'position' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Bạn chưa nhập tiêu đề.',
            'title.max' => 'Tiêu đề không được vượt quá 250 ký tự.',
            'image.required' => 'Bạn chưa chọn ảnh.',
            'image.image' => 'File phải là ảnh hợp lệ.',
            'image.max' => 'Ảnh không được vượt quá 2MB.',
            'link.max' => 'Link không được vượt quá 500 ký tự.',
            'position.required' => 'Bạn chưa nhập vị trí.',
            'position.integer' => 'Vị trí phải là số nguyên.',
            'position.min' => 'Vị trí phải lớn hơn hoặc bằng 0.',
        ];
    }
}
