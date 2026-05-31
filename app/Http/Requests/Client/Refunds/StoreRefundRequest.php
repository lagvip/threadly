<?php

namespace App\Http\Requests\Client\Refunds;

use Illuminate\Foundation\Http\FormRequest;

class StoreRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $this->user() !== null
            && $order
            && (int) $order->user_id === (int) $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:full,partial'],
            'items' => ['nullable', 'array'],
            'items.*.selected' => ['nullable', 'in:1'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:2000'],
            'evidences' => ['required', 'array', 'min:1', 'max:5'],
            'evidences.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:51200'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Vui lòng chọn hình thức hoàn tiền.',
            'type.in' => 'Hình thức hoàn tiền không hợp lệ.',
            'items.*.quantity.integer' => 'Số lượng sản phẩm hoàn phải là số nguyên.',
            'items.*.quantity.min' => 'Số lượng sản phẩm hoàn tối thiểu là 1.',
            'reason.required' => 'Vui lòng chọn lý do hoàn tiền.',
            'evidences.required' => 'Vui lòng tải lên ít nhất một ảnh hoặc video bằng chứng.',
            'evidences.*.mimes' => 'Bằng chứng chỉ hỗ trợ ảnh jpg, jpeg, png, webp hoặc video mp4, mov, webm.',
            'evidences.*.max' => 'Mỗi file bằng chứng tối đa 50MB.',
        ];
    }
}
