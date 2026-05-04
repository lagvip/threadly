<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\RefundRequestEvidence;
use App\Models\RefundRequestItem;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RefundRequestController extends Controller
{
    public function create(Order $order)
    {
        $order->load([
            'details.variant.color',
            'details.variant.size',
            'details.product',
            'refundRequests.evidences',
            'refundRequests.items',
        ]);

        if ((int) $order->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if (!$order->can_request_refund) {
            return redirect()
                ->route('client.orders.index')
                ->with('error', 'Đơn hàng này chưa đủ điều kiện hoặc không còn số tiền để yêu cầu hoàn.');
        }

        // Tính danh sách sản phẩm còn có thể hoàn và số lượng còn được hoàn.
        $refundableItems = $this->buildRefundableItems($order);

        return view('client.refunds.create', compact('order', 'refundableItems'));
    }

    public function store(Request $request, Order $order)
    {
        if ((int) $order->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:full,partial'],
            'items' => ['nullable', 'array'],
            'items.*.selected' => ['nullable', 'in:1'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:2000'],
            'evidences' => ['required', 'array', 'min:1', 'max:5'],
            'evidences.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:51200'],
        ], [
            'type.required' => 'Vui lòng chọn hình thức hoàn tiền.',
            'type.in' => 'Hình thức hoàn tiền không hợp lệ.',
            'items.*.quantity.integer' => 'Số lượng sản phẩm hoàn phải là số nguyên.',
            'items.*.quantity.min' => 'Số lượng sản phẩm hoàn tối thiểu là 1.',
            'reason.required' => 'Vui lòng chọn lý do hoàn tiền.',
            'evidences.required' => 'Vui lòng tải lên ít nhất một ảnh hoặc video bằng chứng.',
            'evidences.*.mimes' => 'Bằng chứng chỉ hỗ trợ ảnh jpg, jpeg, png, webp hoặc video mp4, mov, webm.',
            'evidences.*.max' => 'Mỗi file bằng chứng tối đa 50MB.',
        ]);

        try {
            DB::transaction(function () use ($request, $order, $validated) {
                $order = Order::with([
                        'details.variant.color',
                        'details.variant.size',
                        'details.product',
                        'refundRequests.items',
                    ])
                    ->whereKey($order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $order->user_id !== (int) Auth::id()) {
                    abort(403);
                }

                if (!$order->can_request_refund) {
                    throw new \RuntimeException('Đơn hàng này chưa đủ điều kiện hoặc không còn số tiền để yêu cầu hoàn.');
                }

                // Tính danh sách item được hoàn và tổng tiền yêu cầu hoàn.
                [$selectedItems, $requestedAmount] = $this->resolveRefundSelection(
                    $request,
                    $order,
                    $validated['type']
                );

                // Chặn số tiền hoàn không hợp lệ hoặc vượt số tiền còn có thể hoàn của đơn.
                if ($requestedAmount <= 0 || $requestedAmount > (float) $order->refundable_amount) {
                    throw new \RuntimeException('Số tiền yêu cầu hoàn không hợp lệ.');
                }

                $refundRequest = RefundRequest::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'type' => $validated['type'],
                    'requested_amount' => $requestedAmount,
                    'reason' => trim((string) $validated['reason']),
                    'status' => RefundRequest::STATUS_PENDING,
                ]);

                // Lưu từng sản phẩm được yêu cầu hoàn tiền.
                foreach ($selectedItems as $item) {
                    RefundRequestItem::create([
                        'refund_request_id' => $refundRequest->id,
                        'order_detail_id' => $item['order_detail_id'],
                        'product_name_snapshot' => $item['product_name_snapshot'],
                        'variant_snapshot' => $item['variant_snapshot'],
                        'quantity' => $item['quantity'],
                        'unit_amount' => $item['unit_amount'],
                        'line_amount' => $item['line_amount'],
                    ]);
                }

                // Lưu ảnh/video bằng chứng vào storage và ghi metadata vào database.
                foreach ($request->file('evidences', []) as $file) {
                    $mime = (string) $file->getMimeType();
                    $fileType = Str::startsWith($mime, 'video/') ? 'video' : 'image';
                    $path = $file->store('refund-evidences/' . now()->format('Y/m'), 'public');

                    RefundRequestEvidence::create([
                        'refund_request_id' => $refundRequest->id,
                        'file_type' => $fileType,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $mime,
                        'file_size' => $file->getSize() ?: 0,
                    ]);
                }

                // Đánh dấu đơn đã có yêu cầu hoàn tiền.
                $order->update([
                    'refund_status' => Order::REFUND_REQUESTED,
                    'last_refund_requested_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            // Nếu lỗi thì rollback transaction và trả lại form kèm dữ liệu cũ.
            return back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Gửi yêu cầu hoàn tiền thất bại.');
        }

        return redirect()
            ->route('client.orders.index')
            ->with('success', 'Đã gửi yêu cầu hoàn tiền. Admin sẽ kiểm tra bằng chứng và xử lý.');
    }

    public function wallet()
    {
        // Lấy hoặc tạo ví hoàn tiền cho user hiện tại.
        $wallet = Wallet::firstOrCreate(
            ['user_id' => Auth::id()],
            ['balance' => 0]
        );

        // Load giao dịch liên quan đến đơn hàng và yêu cầu hoàn tiền.
        $wallet->load(['transactions.order', 'transactions.refundRequest']);

        // Lấy lịch sử giao dịch ví, mới nhất lên trước.
        $transactions = $wallet->transactions()
            ->with(['order', 'refundRequest'])
            ->latest('id')
            ->paginate(15);

        // Trả sang trang ví của khách.
        return view('client.wallet.index', compact('wallet', 'transactions'));
    }

    protected function resolveRefundSelection(Request $request, Order $order, string $type): array
    {
        // Lấy danh sách item còn có thể hoàn của đơn.
        $refundableItems = $this->buildRefundableItems($order);
        $selectedItems = [];

        if ($type === RefundRequest::TYPE_FULL) {
            // Hoàn toàn phần: tự chọn toàn bộ item còn số lượng có thể hoàn.
            foreach ($refundableItems as $item) {
                if ($item['available_quantity'] > 0) {
                    $selectedItems[] = [
                        'order_detail_id' => $item['order_detail_id'],
                        'product_name_snapshot' => $item['product_name_snapshot'],
                        'variant_snapshot' => $item['variant_snapshot'],
                        'quantity' => $item['available_quantity'],
                        'unit_amount' => $item['unit_amount'],
                        'line_amount' => round($item['unit_amount'] * $item['available_quantity'], 2),
                    ];
                }
            }

            // Nếu không còn sản phẩm nào có thể hoàn thì báo lỗi.
            if (empty($selectedItems)) {
                throw new \RuntimeException('Đơn hàng này không còn sản phẩm nào có thể hoàn.');
            }

            // Tổng tiền hoàn toàn phần, không vượt quá refundable_amount của đơn.
            $requestedAmount = round(array_sum(array_column($selectedItems, 'line_amount')), 2);
            $requestedAmount = min($requestedAmount, (float) $order->refundable_amount);

            return [$selectedItems, $requestedAmount];
        }

        // Hoàn một phần: lấy item và số lượng khách đã chọn từ form.
        $requestItems = (array) $request->input('items', []);

        foreach ($refundableItems as $item) {
            $detailId = (string) $item['order_detail_id'];
            $input = $requestItems[$detailId] ?? null;

            // Bỏ qua item không được tick chọn.
            if (!$input || (string) ($input['selected'] ?? '') !== '1') {
                continue;
            }

            $quantity = (int) ($input['quantity'] ?? 0);

            // Bỏ qua số lượng không hợp lệ.
            if ($quantity <= 0) {
                continue;
            }

            // Không cho hoàn vượt số lượng còn có thể hoàn của item.
            if ($quantity > $item['available_quantity']) {
                throw new \RuntimeException('Số lượng hoàn của sản phẩm "' . $item['product_name_snapshot'] . '" vượt quá số lượng còn có thể hoàn.');
            }

            // Tính tiền hoàn cho dòng sản phẩm được chọn.
            $lineAmount = round($item['unit_amount'] * $quantity, 2);

            $selectedItems[] = [
                'order_detail_id' => $item['order_detail_id'],
                'product_name_snapshot' => $item['product_name_snapshot'],
                'variant_snapshot' => $item['variant_snapshot'],
                'quantity' => $quantity,
                'unit_amount' => $item['unit_amount'],
                'line_amount' => $lineAmount,
            ];
        }

        // Hoàn một phần bắt buộc phải chọn ít nhất một sản phẩm.
        if (empty($selectedItems)) {
            throw new \RuntimeException('Vui lòng chọn ít nhất một sản phẩm cần hoàn tiền.');
        }

        // Tính tổng tiền yêu cầu hoàn.
        $requestedAmount = round(array_sum(array_column($selectedItems, 'line_amount')), 2);

        return [$selectedItems, $requestedAmount];
    }

    protected function buildRefundableItems(Order $order): array
    {
        // Tính số lượng từng order detail đã được hoàn thành công trước đó.
        $approvedRefundedQuantities = RefundRequestItem::query()
            ->select('refund_request_items.order_detail_id', DB::raw('SUM(refund_request_items.quantity) as refunded_quantity'))
            ->join('refund_requests', 'refund_request_items.refund_request_id', '=', 'refund_requests.id')
            ->where('refund_requests.order_id', $order->id)
            ->where('refund_requests.status', RefundRequest::STATUS_APPROVED)
            ->groupBy('refund_request_items.order_detail_id')
            ->pluck('refunded_quantity', 'order_detail_id');

        // Tổng tiền hàng trước giảm giá.
        $productSubtotal = max((float) $order->details->sum(fn ($detail) => (float) $detail->total), 0);

        // Giới hạn discount không âm và không vượt quá tổng tiền hàng.
        $discount = min(max((float) ($order->discount ?? 0), 0), $productSubtotal);

        $items = [];
        $remainingDiscount = $discount;
        $remainingSubtotal = $productSubtotal;

        // Chuẩn hóa collection details để xử lý chia discount theo từng dòng.
        $details = $order->details->values();

        foreach ($details as $index => $detail) {
            // Số lượng đã mua và số lượng đã hoàn thành công.
            $quantity = max((int) $detail->quantity, 1);
            $refundedQuantity = (int) ($approvedRefundedQuantities[$detail->id] ?? 0);

            // Số lượng còn có thể yêu cầu hoàn.
            $availableQuantity = max(0, $quantity - $refundedQuantity);

            // Tổng tiền dòng sản phẩm.
            $lineTotal = (float) ($detail->total ?? ((float) $detail->unit_price * $quantity));
            $lineTotal = max($lineTotal, 0);

            // Chia discount theo tỷ lệ từng dòng sản phẩm; dòng cuối nhận phần còn lại để tránh lệch làm tròn.
            if ($discount > 0 && $lineTotal > 0 && $productSubtotal > 0) {
                if ($index === $details->count() - 1) {
                    $lineDiscount = $remainingDiscount;
                } else {
                    $lineDiscount = round($discount * ($lineTotal / $productSubtotal), 2);
                    $lineDiscount = min($lineDiscount, $remainingDiscount);
                }
            } else {
                $lineDiscount = 0;
            }

            // Cập nhật phần discount còn lại sau khi chia cho dòng hiện tại.
            $remainingDiscount = max($remainingDiscount - $lineDiscount, 0);
            $remainingSubtotal = max($remainingSubtotal - $lineTotal, 0);

            // Tính số tiền còn có thể hoàn của dòng sau khi trừ discount phân bổ.
            $refundableLineTotal = max($lineTotal - $lineDiscount, 0);
            $unitAmount = $quantity > 0
                ? round($refundableLineTotal / $quantity, 2)
                : (float) $detail->unit_price;

            // Ghép thông tin biến thể để lưu snapshot màu-size.
            $variantParts = [];
            if (optional($detail->variant?->color)->name) {
                $variantParts[] = 'Màu: ' . $detail->variant->color->name;
            }
            if (optional($detail->variant?->size)->name) {
                $variantParts[] = 'Size: ' . $detail->variant->size->name;
            }

            // Trả về dữ liệu item có thể hoàn cho form và cho hàm tính tiền hoàn.
            $items[] = [
                'order_detail_id' => $detail->id,
                'product_name_snapshot' => $detail->product_name ?: optional($detail->product)->name ?: 'Sản phẩm',
                'variant_snapshot' => implode(' | ', $variantParts),
                'ordered_quantity' => $quantity,
                'refunded_quantity' => $refundedQuantity,
                'available_quantity' => $availableQuantity,
                'unit_amount' => $unitAmount,
                'line_amount' => round($unitAmount * $availableQuantity, 2),
            ];
        }

        return $items;
    }
}
