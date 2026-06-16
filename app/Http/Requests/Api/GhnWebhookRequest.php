<?php

namespace App\Http\Requests\Api;

use App\DTOs\Integrations\Ghn\GhnWebhookData;
use Illuminate\Foundation\Http\FormRequest;

class GhnWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '*' => ['nullable'],
        ];
    }

    public function toDTO(): GhnWebhookData
    {
        return GhnWebhookData::fromArray($this->all(), [
            $this->header('X-GHN-Webhook-Secret'),
            $this->header('X-Webhook-Secret'),
            $this->query('secret'),
            $this->input('secret'),
        ]);
    }
}
