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

        $refundableItems = $this->buildRefundableItems($order);

        return view('client.refunds.create', compact('order', 'refundableItems'));
    }

    public function store(Request $request, Order $order)
    {
        if ((int) $order->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $order = Order::with([
                'details.variant.color',
                'details.variant.size',
                'details.product',
                'refundRequests.items',
            ])
            ->whereKey($order->id)
            ->firstOrFail();

        if (!$order->can_request_refund) {
            return back()->with('error', 'Đơn hàng này chưa đủ điều kiện hoặc không còn số tiền để yêu cầu hoàn.');
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

        $refundableItems = $this->buildRefundableItems($order);
        $selectedItems = [];

        if ($validated['type'] === RefundRequest::TYPE_FULL) {
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

            if (empty($selectedItems)) {
                return back()
                    ->withInput()
                    ->with('error', 'Đơn hàng này không còn sản phẩm nào có thể hoàn.');
            }

            $requestedAmount = round(array_sum(array_column($selectedItems, 'line_amount')), 2);
            $requestedAmount = min($requestedAmount, (float) $order->refundable_amount);
        } else {
            $requestItems = (array) $request->input('items', []);

            foreach ($refundableItems as $item) {
                $detailId = (string) $item['order_detail_id'];
                $input = $requestItems[$detailId] ?? null;

                if (!$input || (string) ($input['selected'] ?? '') !== '1') {
                    continue;
                }

                $quantity = (int) ($input['quantity'] ?? 0);

                if ($quantity <= 0) {
                    continue;
                }

                if ($quantity > $item['available_quantity']) {
                    return back()
                        ->withInput()
                        ->with('error', 'Số lượng hoàn của sản phẩm "' . $item['product_name_snapshot'] . '" vượt quá số lượng còn có thể hoàn.');
                }

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

            if (empty($selectedItems)) {
                return back()
                    ->withInput()
                    ->with('error', 'Vui lòng chọn ít nhất một sản phẩm cần hoàn tiền.');
            }

            $requestedAmount = round(array_sum(array_column($selectedItems, 'line_amount')), 2);
        }

        if ($requestedAmount <= 0 || $requestedAmount > (float) $order->refundable_amount) {
            return back()->withInput()->with('error', 'Số tiền yêu cầu hoàn không hợp lệ.');
        }

        DB::transaction(function () use ($request, $order, $validated, $requestedAmount, $selectedItems) {
            $refundRequest = RefundRequest::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'type' => $validated['type'],
                'requested_amount' => $requestedAmount,
                'reason' => trim((string) $validated['reason']),
                'status' => RefundRequest::STATUS_PENDING,
            ]);

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

            $order->update([
                'refund_status' => Order::REFUND_REQUESTED,
                'last_refund_requested_at' => now(),
            ]);
        });

        return redirect()
            ->route('client.orders.index')
            ->with('success', 'Đã gửi yêu cầu hoàn tiền. Admin sẽ kiểm tra bằng chứng và xử lý.');
    }

    public function wallet()
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => Auth::id()],
            ['balance' => 0]
        );

        $wallet->load(['transactions.order', 'transactions.refundRequest']);

        $transactions = $wallet->transactions()
            ->with(['order', 'refundRequest'])
            ->latest('id')
            ->paginate(15);

        return view('client.wallet.index', compact('wallet', 'transactions'));
    }

    protected function buildRefundableItems(Order $order): array
    {
        $approvedRefundedQuantities = RefundRequestItem::query()
            ->select('refund_request_items.order_detail_id', DB::raw('SUM(refund_request_items.quantity) as refunded_quantity'))
            ->join('refund_requests', 'refund_request_items.refund_request_id', '=', 'refund_requests.id')
            ->where('refund_requests.order_id', $order->id)
            ->where('refund_requests.status', RefundRequest::STATUS_APPROVED)
            ->groupBy('refund_request_items.order_detail_id')
            ->pluck('refunded_quantity', 'order_detail_id');

        $productSubtotal = max((float) $order->details->sum(fn ($detail) => (float) $detail->total), 0);
        $discount = min(max((float) ($order->discount ?? 0), 0), $productSubtotal);

        $items = [];
        $remainingDiscount = $discount;
        $remainingSubtotal = $productSubtotal;

        $details = $order->details->values();

        foreach ($details as $index => $detail) {
            $quantity = max((int) $detail->quantity, 1);
            $refundedQuantity = (int) ($approvedRefundedQuantities[$detail->id] ?? 0);
            $availableQuantity = max(0, $quantity - $refundedQuantity);

            $lineTotal = (float) ($detail->total ?? ((float) $detail->unit_price * $quantity));
            $lineTotal = max($lineTotal, 0);

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

            $remainingDiscount = max($remainingDiscount - $lineDiscount, 0);
            $remainingSubtotal = max($remainingSubtotal - $lineTotal, 0);

            $refundableLineTotal = max($lineTotal - $lineDiscount, 0);
            $unitAmount = $quantity > 0
                ? round($refundableLineTotal / $quantity, 2)
                : (float) $detail->unit_price;

            $variantParts = [];
            if (optional($detail->variant?->color)->name) {
                $variantParts[] = 'Màu: ' . $detail->variant->color->name;
            }
            if (optional($detail->variant?->size)->name) {
                $variantParts[] = 'Size: ' . $detail->variant->size->name;
            }

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

