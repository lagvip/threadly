@extends('admin.layouts.layout')

@section('content')
    <style>
        .refund-line {
            font-size: 13px;
            line-height: 1.35;
        }

        .refund-chip {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .refund-chip-full {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .refund-chip-partial {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .refund-chip-none {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
    </style>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $statusLabels = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đang giao hàng',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
            'waiting_for_cancellation' => 'Chờ duyệt hủy',
        ];

        $paymentLabels = [
            'paid' => 'Đã thanh toán',
            'unpaid' => 'Chưa thanh toán',
            'pending' => 'Đang chờ thanh toán',
            'failed' => 'Thanh toán thất bại',
            'cancelled' => 'Thanh toán đã hủy',
            'expired' => 'Thanh toán hết hạn',
        ];

        $approvedRefundByDetail = \App\Models\RefundRequestItem::query()
            ->join('refund_requests', 'refund_requests.id', '=', 'refund_request_items.refund_request_id')
            ->where('refund_requests.order_id', $order->id)
            ->where('refund_requests.status', \App\Models\RefundRequest::STATUS_APPROVED)
            ->groupBy('refund_request_items.order_detail_id')
            ->selectRaw('refund_request_items.order_detail_id')
            ->selectRaw('COALESCE(SUM(refund_request_items.quantity), 0) as refunded_quantity')
            ->selectRaw('COALESCE(SUM(refund_request_items.line_amount), 0) as refunded_amount')
            ->get()
            ->keyBy('order_detail_id');

        $subtotal = $order->details->sum(fn($d) => (float) $d->total);
        $discount = (float) ($order->discount ?? 0);
        $shippingFee = (float) ($order->shipping_fee ?? 0);
        $refundedAmount = (float) ($order->refunded_amount ?? 0);
        $refundableAmount = (float) ($order->refundable_amount ?? 0);
        $netPaidAmount = (float) ($order->net_paid_amount ?? max((float) $order->total_price - $refundedAmount, 0));
    @endphp

    <div class="container py-4">
        <div class="card mb-4 p-3">
            <h5><strong>Đơn hàng:</strong> {{ $order->order_code }}</h5>
            <p class="mb-1">Ngày tạo: {{ $order->created_at->format('d/m/Y H:i') }}</p>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-primary">
                    {{ $order->payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : strtoupper($order->payment_method) }}
                </span>

                <span class="badge bg-success">
                    {{ $paymentLabels[$order->payment_status] ?? ucfirst($order->payment_status) }}
                </span>

                <span class="badge bg-warning text-dark">
                    {{ $statusLabels[$order->order_status] ?? ucfirst($order->order_status) }}
                </span>

                @if(($order->refund_status ?? 'none') !== 'none')
                    <span class="badge bg-danger">
                        {{ $order->refund_status_label }}
                    </span>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">Sản phẩm</div>
                    <div class="card-body">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Giá</th>
                                    <th>Thành tiền gốc</th>
                                    <th>Hoàn tiền</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($order->details as $item)
                                    @php
                                        $product = $item->variant?->product;
                                        $size = $item->variant?->size?->name ?? '-';
                                        $color = $item->variant?->color?->name ?? '-';
                                        $image = $product?->image_primary
                                            ? asset('storage/' . $product->image_primary)
                                            : 'https://via.placeholder.com/50x50?text=No+Image';

                                        $orderedQty = (int) ($item->quantity ?? 0);
                                        $itemTotal = (float) ($item->total ?? 0);

                                        $refundRow = $approvedRefundByDetail->get($item->id);
                                        $refundedQty = (int) ($refundRow->refunded_quantity ?? 0);
                                        $refundedLineAmount = (float) ($refundRow->refunded_amount ?? 0);

                                        $remainingQty = max($orderedQty - $refundedQty, 0);
                                        $remainingAmount = max($itemTotal - $refundedLineAmount, 0);

                                        $isFullRefundedItem = $orderedQty > 0 && $remainingQty <= 0 && $refundedQty > 0;
                                        $isPartialRefundedItem = $refundedQty > 0 && $remainingQty > 0;
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $image }}"
                                                     alt="{{ $product?->name ?? $item->product_name }}"
                                                     class="rounded"
                                                     style="width:50px;height:50px;object-fit:cover">

                                                <div>
                                                    <div class="fw-bold">{{ $product?->name ?? $item->product_name }}</div>
                                                    <small class="text-muted d-block">Size: {{ $size }}</small>
                                                    <small class="text-muted d-block">Color: {{ $color }}</small>

                                                    @if($isFullRefundedItem)
                                                        <span class="refund-chip refund-chip-full mt-1">Đã hoàn hết sản phẩm này</span>
                                                    @elseif($isPartialRefundedItem)
                                                        <span class="refund-chip refund-chip-partial mt-1">Đã hoàn một phần</span>
                                                    @else
                                                        <span class="refund-chip refund-chip-none mt-1">Chưa hoàn sản phẩm này</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div><strong>Đã mua:</strong> {{ $orderedQty }}</div>

                                            @if($refundedQty > 0)
                                                <div class="refund-line text-danger">
                                                    Đã hoàn: {{ $refundedQty }}
                                                </div>

                                                <div class="refund-line text-success fw-semibold">
                                                    Còn tính: {{ $remainingQty }}
                                                </div>
                                            @endif
                                        </td>

                                        <td>{{ number_format($item->unit_price, 0, ',', '.') }} VNĐ</td>

                                        <td>
                                            <div>{{ number_format($itemTotal, 0, ',', '.') }} VNĐ</div>

                                            @if($refundedLineAmount > 0)
                                                <div class="refund-line text-danger">
                                                    -{{ number_format($refundedLineAmount, 0, ',', '.') }} VNĐ
                                                </div>

                                                <div class="refund-line text-success fw-semibold">
                                                    Còn: {{ number_format($remainingAmount, 0, ',', '.') }} VNĐ
                                                </div>
                                            @endif
                                        </td>

                                        <td>
                                            @if($refundedLineAmount > 0)
                                                <div class="text-danger fw-semibold">
                                                    Đã hoàn {{ number_format($refundedLineAmount, 0, ',', '.') }} VNĐ
                                                </div>

                                                <div class="small text-muted">
                                                    {{ $refundedQty }} / {{ $orderedQty }} sản phẩm
                                                </div>
                                            @else
                                                <span class="text-muted">Chưa hoàn</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Không có sản phẩm</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-3 shadow-sm">
                    <div class="card-header fw-bold">Tổng kết thanh toán</div>
                    <div class="card-body">
                        <p><strong>Tổng tiền hàng gốc:</strong> {{ number_format($subtotal, 0, ',', '.') }} VNĐ</p>
                        <p><strong>Phí vận chuyển:</strong> {{ number_format($shippingFee, 0, ',', '.') }} VNĐ</p>
                        <p><strong>Giảm giá:</strong> {{ number_format($discount, 0, ',', '.') }} VNĐ</p>

                        <hr>

                        <h5 class="mt-3 text-primary fw-bold">
                            Tổng khách đã thanh toán: {{ number_format($order->total_price, 0, ',', '.') }} VNĐ
                        </h5>

                        @if($refundedAmount > 0)
                            <p class="mb-1 text-danger fw-semibold">
                                Đã hoàn tiền sản phẩm: -{{ number_format($refundedAmount, 0, ',', '.') }} VNĐ
                            </p>

                            <h5 class="mt-2 text-success fw-bold">
                                Thực thu sau hoàn: {{ number_format($netPaidAmount, 0, ',', '.') }} VNĐ
                            </h5>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">Thông tin người nhận</div>
                    <div class="card-body">
                        <p><strong>Tên người nhận:</strong> {{ $order->name }}</p>
                        <p><strong>Email:</strong> {{ $order->email }}</p>
                        <p><strong>SĐT:</strong> {{ $order->phone }}</p>
                        <p><strong>Địa chỉ:</strong> {{ $order->address }}</p>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">Thông tin vận chuyển GHN</div>

                    <div class="card-body">
                        @if ($order->ghn_order_code)
                            <p class="mb-2">
                                <strong>Mã vận đơn GHN:</strong>
                                <span class="text-primary fw-bold">{{ $order->ghn_order_code }}</span>
                            </p>

                            <p class="mb-2">
                                <strong>Mã đơn nội bộ gửi GHN:</strong>
                                <span class="text-muted">{{ $order->ghn_client_order_code ?: '-' }}</span>
                            </p>

                            <p class="mb-2">
                                <strong>Nhóm trạng thái GHN:</strong>
                                <span class="badge {{ $order->ghn_status_group_badge }}">
                                    {{ $order->ghn_status_group }}
                                </span>
                            </p>

                            <p class="mb-2">
                                <strong>Trạng thái chi tiết:</strong>
                                {{ $order->ghn_status_name ?: ($order->ghn_status ?: '-') }}

                                @if ($order->ghn_status)
                                    <small class="text-muted">({{ $order->ghn_status }})</small>
                                @endif
                            </p>

                            <p class="mb-2">
                                <strong>Dự kiến giao:</strong>
                                {{ $order->ghn_expected_delivery_time ? $order->ghn_expected_delivery_time->format('d/m/Y H:i') : '-' }}
                            </p>

                            <p class="mb-3">
                                <strong>Cập nhật lúc:</strong>
                                {{ $order->ghn_synced_at ? $order->ghn_synced_at->format('d/m/Y H:i') : 'Chưa đồng bộ' }}
                            </p>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <form method="POST" action="{{ route('orders.ghn.sync', $order->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        Đồng bộ trạng thái GHN
                                    </button>
                                </form>

                                <a href="{{ route('orders.ghn.print', $order->id) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-secondary">
                                    In vận đơn GHN
                                </a>

                                @if (!in_array($order->ghn_status, ['delivered', 'cancel', 'returned', 'lost', 'damage'], true))
                                    <form method="POST"
                                          action="{{ route('orders.ghn.cancel', $order->id) }}"
                                          onsubmit="return confirm('Anh chắc chắn muốn hủy vận đơn GHN này?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Hủy vận đơn GHN
                                        </button>
                                    </form>
                                @endif
                            </div>

                            @if (app()->environment('local'))
                                <hr>

                                <div class="mt-3">
                                    <div class="fw-bold mb-2 text-danger">
                                        Giả lập trạng thái GHN local
                                    </div>

                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach([
                                            'ready_to_pick' => 'Chờ bàn giao',
                                            'picked' => 'Đã lấy hàng',
                                            'delivering' => 'Đang giao',
                                            'delivery_fail' => 'Giao thất bại',
                                            'waiting_to_return' => 'Chờ hoàn hàng',
                                            'returned' => 'Đã hoàn hàng',
                                            'delivered' => 'Hoàn tất',
                                            'cancel' => 'Đơn hủy',
                                            'lost' => 'Thất lạc',
                                            'damage' => 'Hư hỏng',
                                        ] as $simulateStatus => $simulateLabel)
                                            <form method="POST"
                                                  action="{{ route('orders.ghn.simulate', [$order->id, $simulateStatus]) }}"
                                                  @if($simulateStatus === 'cancel')
                                                      onsubmit="return confirm('Giả lập GHN hủy đơn này?')"
                                                  @endif>
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    Giả lập: {{ $simulateLabel }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>

                                    <small class="text-muted d-block mt-2">
                                        Các nút này chỉ đổi trạng thái trong Laravel để test webhook/local, không đổi trạng thái thật trên GHN.
                                    </small>
                                </div>
                            @endif
                        @else
                            <p class="text-muted mb-3">Đơn này chưa gửi sang GHN.</p>

                            <form method="POST" action="{{ route('orders.ghn.create', $order->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    Tạo vận đơn GHN
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if(filled($order->cancel_reason))
                    <div class="card shadow-sm mb-3">
                        <div class="card-header fw-bold">Lý do hủy đơn</div>
                        <div class="card-body">
                            <span class="badge bg-secondary">
                                {{ $order->cancel_reason }}
                            </span>
                        </div>
                    </div>
                @endif

                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">Hoàn tiền VNPay demo</div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Trạng thái hoàn:</strong> {{ $order->refund_status_label }}</p>
                        <p class="mb-2"><strong>Đã hoàn sản phẩm:</strong> {{ number_format($refundedAmount, 0, ',', '.') }} VNĐ</p>
                        <p class="mb-2"><strong>Còn có thể hoàn sản phẩm:</strong> {{ number_format($refundableAmount, 0, ',', '.') }} VNĐ</p>
                        @if($order->last_refund_requested_at)
                            <p class="mb-2"><strong>Yêu cầu gần nhất:</strong> {{ $order->last_refund_requested_at->format('d/m/Y H:i') }}</p>
                        @endif

                        @if($order->last_refunded_at)
                            <p class="mb-3"><strong>Hoàn gần nhất:</strong> {{ $order->last_refunded_at->format('d/m/Y H:i') }}</p>
                        @endif

                        <a href="{{ route('admin.refunds.index', ['keyword' => $order->order_code]) }}" class="btn btn-sm btn-outline-primary">
                            Xem yêu cầu hoàn tiền
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header fw-bold">Voucher áp dụng</div>
                    <div class="card-body">
                        @php
                            $appliedDiscount = (float) ($order->discount ?? 0);
                            $voucherTypeLabel = [
                                'percent' => 'Giảm theo phần trăm',
                                'fixed' => 'Giảm số tiền cố định',
                            ];
                        @endphp

                        @if ($order->voucher_code)
                            <p class="mb-2">
                                <strong>Mã voucher:</strong>
                                <span class="badge bg-success">{{ $order->voucher_code }}</span>
                            </p>

                            <p class="mb-2">
                                <strong>Số tiền đã giảm:</strong>
                                {{ number_format($appliedDiscount, 0, ',', '.') }} VNĐ
                            </p>

                            @if ($order->voucher)
                                <p class="mb-2">
                                    <strong>Loại voucher:</strong>
                                    {{ $voucherTypeLabel[$order->voucher->type] ?? $order->voucher->type }}
                                </p>

                                <p class="mb-0">
                                    <strong>Giá trị voucher:</strong>
                                    @if ($order->voucher->type === 'percent')
                                        {{ rtrim(rtrim(number_format((float) $order->voucher->value, 2, '.', ''), '0'), '.') }}%
                                    @else
                                        {{ number_format((float) $order->voucher->value, 0, ',', '.') }} VNĐ
                                    @endif
                                </p>
                            @endif
                        @else
                            <p class="mb-0 text-muted">Đơn hàng không áp dụng voucher.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
