<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:250'],
            'image' => ['nullable', 'image', 'max:2048'],
            'link' => ['nullable', 'string', 'max:500'],
            'position' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề không được để trống.',
            'title.max' => 'Tiêu đề không được vượt quá 250 ký tự.',
            'image.image' => 'File tải lên phải là hình ảnh.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'link.max' => 'Link không được vượt quá 500 ký tự.',
            'position.required' => 'Vị trí không được để trống.',
            'position.integer' => 'Vị trí phải là số nguyên.',
            'position.min' => 'Vị trí phải lớn hơn hoặc bằng 0.',
        ];
    }
}
