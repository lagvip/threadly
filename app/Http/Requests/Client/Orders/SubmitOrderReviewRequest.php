<?php

namespace App\Http\Requests\Client\Orders;

use Illuminate\Foundation\Http\FormRequest;

class SubmitOrderReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Số sao đánh giá không hợp lệ.',
            'rating.min' => 'Đánh giá tối thiểu là 1 sao.',
            'rating.max' => 'Đánh giá tối đa là 5 sao.',
            'comment.required' => 'Vui lòng nhập nội dung bình luận.',
            'comment.max' => 'Bình luận tối đa 1000 ký tự.',
        ];
    }
}
