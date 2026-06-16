<?php

namespace App\Services\Integrations\Gemini;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function generate(string $prompt): string
    {
        // Lấy cấu hình Gemini từ config/services.php, thường lấy từ .env.
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model');
        $baseUrl = rtrim(config('services.gemini.base_url'), '/');

        // Nếu chưa cấu hình API key thì báo lỗi, không gọi Gemini.
        if (empty($apiKey)) {
            throw new \RuntimeException('Thiếu GEMINI_API_KEY trong file .env');
        }

        // Gửi request sang Gemini API để sinh câu trả lời.
        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])
            // Timeout 30 giây để tránh request treo quá lâu.
            ->timeout(30)

            // Nếu lỗi tạm thời thì thử lại 2 lần, mỗi lần cách nhau 1000ms.
            ->retry(2, 1000)

            // Gọi endpoint generateContent của model Gemini.
            ->post("{$baseUrl}/models/{$model}:generateContent", [
                // System instruction dùng để ép AI trả lời đúng vai trò của website bán hàng.
                'system_instruction' => [
                    'parts' => [
                        [
                            'text' => 'Bạn là trợ lý AI cho website bán hàng Laravel.
                                        Chỉ trả lời dựa trên dữ liệu được cung cấp.
                                        Không bịa thông tin.
                                        Không dùng markdown như ** hoặc ###.
                                        Nếu thiếu dữ liệu thì nói rõ là chưa đủ dữ liệu.',
                        ],
                    ],
                ],

                // Nội dung câu hỏi thật sự được gửi sang Gemini.
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],

                // Cấu hình cách Gemini sinh câu trả lời.
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 1200,
                ],
            ])

            // Nếu API trả lỗi HTTP thì throw exception để controller/service phía trên bắt lỗi.
            ->throw();

        // Chuyển response JSON của Gemini thành array PHP.
        $json = $response->json();

        // Log response thô để debug khi Gemini trả lỗi, trả rỗng hoặc bị cắt output.
        Log::info('Gemini raw response', [
            'finishReason' => data_get($json, 'candidates.0.finishReason'),
            'usageMetadata' => data_get($json, 'usageMetadata'),
            'response' => $json,
        ]);

        // Lấy các phần text trong câu trả lời của Gemini.
        $parts = data_get($json, 'candidates.0.content.parts', []);

        // Gom các part text lại thành một chuỗi hoàn chỉnh.
        $text = collect($parts)
            ->map(function ($part) {
                return is_array($part) ? ($part['text'] ?? '') : '';
            })
            ->filter()
            ->implode('');

        // Nếu Gemini không trả text thì trả thông báo fallback cho user.
        if (blank($text)) {
            return 'Xin lỗi, tôi chưa lấy được câu trả lời từ Gemini.';
        }

        // Lấy lý do Gemini dừng sinh câu trả lời.
        $finishReason = data_get($json, 'candidates.0.finishReason');

        // Nếu bị cắt vì vượt maxOutputTokens thì báo cho user biết câu trả lời chưa đầy đủ.
        if ($finishReason === 'MAX_TOKENS') {
            $text .= "\n\n[Phản hồi bị cắt do quá dài. Bạn hãy hỏi ngắn hơn hoặc chia nhỏ câu hỏi.]";
        }

        // Trả câu trả lời cuối cùng về ShopChatService/ChatbotController.
        return $text;
    }
}
