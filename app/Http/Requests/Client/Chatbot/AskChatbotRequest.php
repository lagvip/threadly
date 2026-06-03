<?php

namespace App\Http\Requests\Client\Chatbot;

use Illuminate\Foundation\Http\FormRequest;

class AskChatbotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:1000'],
        ];
    }
}
