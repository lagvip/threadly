<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

class ShopChatService
{
    public function __construct(
        protected GeminiService $geminiService
    ) {}

    public function reply(User $user, string $message): string
    {
        $message = trim($message);
        $normalized = Str::lower($message);

        $context = $this->buildContext($user, $message, $normalized);

        $prompt = <<<PROMPT
Câu hỏi của người dùng:
{$message}

Dữ liệu nội bộ:
{$context}

Yêu cầu trả lời:
- Trả lời bằng tiếng Việt, ngắn gọn, dễ hiểu.
- Nếu là câu hỏi về đơn hàng thì chỉ trả lời trong phạm vi đơn hàng của chính người dùng hiện tại.
- Nếu không đủ dữ liệu, phải nói rõ là chưa đủ dữ liệu.
- Không bịa giá, không bịa tồn kho, không bịa trạng thái.
- Không đề xuất thao tác cập nhật database.
PROMPT;

        return $this->geminiService->generate($prompt);
    }

    protected function buildContext(User $user, string $message, string $normalized): string
    {
        $blocks = [];
        $blocks[] = "Người dùng hiện tại: ID {$user->id}, tên {$user->name}, email {$user->email}";

        if ($this->isOrderQuestion($normalized)) {
            $blocks[] = $this->buildOrderContext($user);
        } else {
            $blocks[] = $this->buildProductContext($message);
            $blocks[] = $this->buildOrderContext($user);
        }

        return implode("\n\n", $blocks);
    }

    protected function isOrderQuestion(string $text): bool
    {
        $keywords = [
            'đơn hàng',
            'mã đơn',
            'order',
            'thanh toán',
            'vnpay',
            'hủy đơn',
            'trạng thái đơn',
            'paid',
            'pending',
            'processing',
            'shipped',
            'delivered',
        ];

        foreach ($keywords as $keyword) {
            if (Str::contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    protected function buildOrderContext(User $user): string
    {
        $orders = Order::with([
                'details.product',
                'details.variant.color',
                'details.variant.size',
            ])
            ->where('user_id', $user->id)
            ->latest('id')
            ->take(3)
            ->get();

        if ($orders->isEmpty()) {
            return "Đơn hàng gần đây: người dùng chưa có đơn hàng nào.";
        }

        $lines = ["Đơn hàng gần đây của người dùng:"];

        foreach ($orders as $order) {
            $lines[] = "- Đơn #{$order->order_code} | trạng thái đơn: {$order->order_status} | trạng thái thanh toán: {$order->payment_status} | tổng tiền: {$order->total_price}";

            foreach ($order->details->take(3) as $detail) {
                $variantText = '';

                if ($detail->variant) {
                    $color = optional($detail->variant->color)->name;
                    $size = optional($detail->variant->size)->name;

                    $parts = array_filter([$color, $size]);
                    $variantText = $parts ? ' (' . implode(' - ', $parts) . ')' : '';
                }

                $lines[] = "  + {$detail->product_name}{$variantText} | SL: {$detail->quantity} | giá: {$detail->unit_price}";
            }
        }

        return implode("\n", $lines);
    }

    protected function buildProductContext(string $message): string
    {
        $keywords = $this->extractKeywords($message);

        $query = Product::with([
                'brand:id,name',
                'category:id,name',
                'variants' => function ($q) {
                    $q->where('status', 'active')->orderBy('price', 'asc');
                },
            ])
            ->where('status', 'active');

        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                }
            });
        }

        $products = $query->latest('id')->take(6)->get();

        if ($products->isEmpty()) {
            $products = Product::with([
                    'brand:id,name',
                    'category:id,name',
                    'variants' => function ($q) {
                        $q->where('status', 'active')->orderBy('price', 'asc');
                    },
                ])
                ->where('status', 'active')
                ->latest('id')
                ->take(6)
                ->get();
        }

        if ($products->isEmpty()) {
            return "Sản phẩm: hiện chưa lấy được dữ liệu sản phẩm.";
        }

        $lines = ["Danh sách sản phẩm tham chiếu:"];

        foreach ($products as $product) {
            $firstVariant = $product->variants->first();
            $price = $firstVariant?->price;
            $qty = $firstVariant?->quantity;

            $lines[] = "- {$product->name} | hãng: " . ($product->brand->name ?? 'N/A')
                . " | danh mục: " . ($product->category->name ?? 'N/A')
                . " | giá thấp nhất: " . ($price ?? 'N/A')
                . " | tồn đầu variant: " . ($qty ?? 'N/A');
        }

        return implode("\n", $lines);
    }

    protected function extractKeywords(string $message): array
    {
        $message = Str::lower($message);
        $message = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $message);
        $parts = preg_split('/\s+/u', $message, -1, PREG_SPLIT_NO_EMPTY);

        $stopWords = [
            'tôi', 'muốn', 'hỏi', 'về', 'của', 'là', 'có', 'không', 'cho', 'xin',
            'xem', 'giúp', 'mình', 'em', 'anh', 'chị', 'sản', 'phẩm', 'đơn', 'hàng',
            'trạng', 'thái', 'bao', 'nhiêu', 'thế', 'nào'
        ];

        $keywords = array_values(array_filter($parts, function ($word) use ($stopWords) {
            return !in_array($word, $stopWords, true) && mb_strlen($word) >= 2;
        }));

        return array_slice(array_unique($keywords), 0, 5);
    }
}
