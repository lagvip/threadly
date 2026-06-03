<?php

namespace App\Http\Requests\Admin\Reviews;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'admin_reply' => ['required', 'string', 'max:1000'],
        ];
    }
}
