<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function generate(string $prompt): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model');
        $baseUrl = rtrim(config('services.gemini.base_url'), '/');

        if (empty($apiKey)) {
            throw new \RuntimeException('Thiếu GEMINI_API_KEY trong file .env');
        }

        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type'   => 'application/json',
        ])
            ->timeout(30)
            ->retry(2, 1000)
            ->post("{$baseUrl}/models/{$model}:generateContent", [
                'system_instruction' => [
                    'parts' => [
                        [
                            'text' => 'Bạn là trợ lý AI cho website bán hàng Laravel.
                                        Chỉ trả lời dựa trên dữ liệu được cung cấp.
                                        Không bịa thông tin.
                                        Không dùng markdown như ** hoặc ###.
                                        Nếu thiếu dữ liệu thì nói rõ là chưa đủ dữ liệu.'
                        ]
                    ]
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 1200,
                ],
            ])
            ->throw();

        $json = $response->json();

        Log::info('Gemini raw response', [
            'finishReason' => data_get($json, 'candidates.0.finishReason'),
            'usageMetadata' => data_get($json, 'usageMetadata'),
            'response' => $json,
        ]);

        $parts = data_get($json, 'candidates.0.content.parts', []);

        $text = collect($parts)
            ->map(function ($part) {
                return is_array($part) ? ($part['text'] ?? '') : '';
            })
            ->filter()
            ->implode('');

        if (blank($text)) {
            return 'Xin lỗi, tôi chưa lấy được câu trả lời từ Gemini.';
        }

        $finishReason = data_get($json, 'candidates.0.finishReason');

        if ($finishReason === 'MAX_TOKENS') {
            $text .= "\n\n[Phản hồi bị cắt do quá dài. Bạn hãy hỏi ngắn hơn hoặc chia nhỏ câu hỏi.]";
        }

        return $text;
    }
}
