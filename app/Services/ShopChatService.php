<?php

namespace App\Services;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Str;

class ShopChatService
{
    public function __construct(
        protected GeminiService $geminiService,
        protected OrderRepositoryInterface $orders,
        protected ProductRepositoryInterface $products,
    ) {}

    public function reply(User $user, string $message): string
    {
        // Chuẩn hóa câu hỏi người dùng để xử lý keyword.
        $message = trim($message);
        $normalized = Str::lower($message);

        // Tạo dữ liệu nội bộ liên quan đến câu hỏi: user, đơn hàng, sản phẩm.
        $context = $this->buildContext($user, $message, $normalized);

        // Ghép câu hỏi + dữ liệu nội bộ + yêu cầu trả lời thành prompt gửi cho Gemini.
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

        // Gửi prompt sang GeminiService và trả câu trả lời về controller.
        return $this->geminiService->generate($prompt);
    }

    protected function buildContext(User $user, string $message, string $normalized): string
    {
        $blocks = [];

        // Luôn đưa thông tin user hiện tại vào context để AI biết đang trả lời cho ai.
        $blocks[] = "Người dùng hiện tại: ID {$user->id}, tên {$user->name}, email {$user->email}";

        // Nếu câu hỏi liên quan đơn hàng thì chỉ lấy dữ liệu đơn của chính user.
        if ($this->isOrderQuestion($normalized)) {
            $blocks[] = $this->buildOrderContext($user);
        } else {
            // Nếu không phải câu hỏi đơn hàng thì lấy thêm dữ liệu sản phẩm để tư vấn.
            $blocks[] = $this->buildProductContext($message);

            // Vẫn thêm đơn hàng gần đây để AI có ngữ cảnh nếu user hỏi lẫn sản phẩm/đơn.
            $blocks[] = $this->buildOrderContext($user);
        }

        // Ghép các khối dữ liệu thành một đoạn context.
        return implode("\n\n", $blocks);
    }

    protected function isOrderQuestion(string $text): bool
    {
        // Danh sách keyword để nhận biết câu hỏi liên quan đơn hàng/thanh toán.
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

        // Nếu câu hỏi chứa một trong các keyword trên thì coi là câu hỏi về đơn hàng.
        foreach ($keywords as $keyword) {
            if (Str::contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    protected function buildOrderContext(User $user): string
    {
        // Lấy 3 đơn hàng gần nhất của user hiện tại, kèm sản phẩm và biến thể màu-size.
        $orders = $this->orders->recentForUserWithDetails((int) $user->id, 3);

        // Nếu user chưa có đơn thì đưa thông tin rõ ràng vào context.
        if ($orders->isEmpty()) {
            return "Đơn hàng gần đây: người dùng chưa có đơn hàng nào.";
        }

        $lines = ["Đơn hàng gần đây của người dùng:"];

        // Tạo mô tả ngắn cho từng đơn hàng.
        foreach ($orders as $order) {
            $lines[] = "- Đơn #{$order->order_code} | trạng thái đơn: {$order->order_status} | trạng thái thanh toán: {$order->payment_status} | tổng tiền: {$order->total_price}";

            // Mỗi đơn chỉ lấy tối đa 3 sản phẩm để prompt không quá dài.
            foreach ($order->details->take(3) as $detail) {
                $variantText = '';

                // Nếu có biến thể thì lấy màu và size để AI trả lời rõ sản phẩm nào.
                if ($detail->variant) {
                    $color = optional($detail->variant->color)->name;
                    $size = optional($detail->variant->size)->name;

                    $parts = array_filter([$color, $size]);
                    $variantText = $parts ? ' (' . implode(' - ', $parts) . ')' : '';
                }

                // Thêm dòng sản phẩm trong đơn vào context.
                $lines[] = "  + {$detail->product_name}{$variantText} | SL: {$detail->quantity} | giá: {$detail->unit_price}";
            }
        }

        return implode("\n", $lines);
    }

    protected function buildProductContext(string $message): string
    {
        // Tách keyword từ câu hỏi để tìm sản phẩm liên quan.
        $keywords = $this->extractKeywords($message);

        // Lấy tối đa 6 sản phẩm mới nhất phù hợp.
        $products = $this->products->activeForChat($keywords, 6);

        // Nếu không tìm được theo keyword thì fallback lấy 6 sản phẩm active mới nhất.
        if ($products->isEmpty()) {
            $products = $this->products->activeForChat([], 6);
        }

        // Nếu vẫn không có sản phẩm thì báo rõ chưa lấy được dữ liệu.
        if ($products->isEmpty()) {
            return "Sản phẩm: hiện chưa lấy được dữ liệu sản phẩm.";
        }

        $lines = ["Danh sách sản phẩm tham chiếu:"];

        // Tạo context sản phẩm gồm tên, hãng, danh mục, giá thấp nhất và tồn kho biến thể đầu.
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
        // Chuyển câu hỏi về chữ thường.
        $message = Str::lower($message);

        // Xóa ký tự đặc biệt, chỉ giữ chữ, số và khoảng trắng.
        $message = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $message);

        // Tách câu thành các từ riêng lẻ.
        $parts = preg_split('/\s+/u', $message, -1, PREG_SPLIT_NO_EMPTY);

        // Các từ quá chung chung sẽ bị loại để tìm kiếm sản phẩm chính xác hơn.
        $stopWords = [
            'tôi', 'muốn', 'hỏi', 'về', 'của', 'là', 'có', 'không', 'cho', 'xin',
            'xem', 'giúp', 'mình', 'em', 'anh', 'chị', 'sản', 'phẩm', 'đơn', 'hàng',
            'trạng', 'thái', 'bao', 'nhiêu', 'thế', 'nào'
        ];

        // Chỉ giữ từ không nằm trong stopWords và dài từ 2 ký tự trở lên.
        $keywords = array_values(array_filter($parts, function ($word) use ($stopWords) {
            return !in_array($word, $stopWords, true) && mb_strlen($word) >= 2;
        }));

        // Loại trùng và chỉ lấy tối đa 5 keyword để query không quá rộng.
        return array_slice(array_unique($keywords), 0, 5);
    }
}
