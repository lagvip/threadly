<?php

namespace App\Http\Requests\Client\Wishlist;

use Illuminate\Foundation\Http\FormRequest;

class StoreWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'variant_id.required' => 'Vui lòng chọn màu và kích thước trước khi thêm vào yêu thích.',
            'variant_id.exists' => 'Biến thể sản phẩm không tồn tại.',
        ];
    }
}
