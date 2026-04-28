@extends('admin.layouts.layout')

@section('content')
    <style>
        .orders-table {
            width: 100%;
            table-layout: fixed;
        }

        .orders-table th,
        .orders-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .orders-table .col-code {
            width: 13%;
        }

        .orders-table .col-date {
            width: 9%;
        }

        .orders-table .col-customer {
            width: 19%;
        }

        .orders-table .col-total {
            width: 11%;
        }

        .orders-table .col-payment {
            width: 15%;
        }

        .orders-table .col-status {
            width: 11%;
        }

        .orders-table .col-ghn {
            width: 12%;
        }

        .orders-table .col-action {
            width: 10%;
        }

        .customer-email,
        .order-code-text,
        .ghn-code-text {
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }

        .order-actions {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .order-actions form {
            display: inline-flex;
            margin: 0;
        }

        .order-actions .btn {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .order-actions .btn i {
            font-size: 13px;
        }

        .badge-order {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ghn-info {
            line-height: 1.25;
        }

        .ghn-info .badge {
            font-size: 11px;
            padding: 3px 7px;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .refund-money-line {
            font-size: 12px;
            line-height: 1.25;
        }

        .refund-status-badge {
            font-size: 11px;
            padding: 4px 8px;
        }

        .table-responsive {
            overflow-x: visible;
        }

        @media (max-width: 1400px) {
            .order-actions {
                gap: 3px;
            }

            .order-actions .btn {
                width: 28px;
                height: 28px;
            }

            .orders-table {
                font-size: 13px;
            }

            .badge.rounded-pill {
                padding-left: 0.65rem !important;
                padding-right: 0.65rem !important;
            }

            .refund-status-badge {
                font-size: 10px;
                padding: 3px 6px;
            }
        }
    </style>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="container py-4">
        <h3 class="fs-4 fw-semibold mb-4">Tổng quan đơn hàng</h3>

        <div class="row g-4 mb-5">
            @php
                $statuses = [
                    ['count' => $orderCancel, 'label' => 'Đã hủy', 'icon' => 'cart-x', 'color' => 'danger'],
                    ['count' => $orderDelivering, 'label' => 'Đang giao', 'icon' => 'truck', 'color' => 'info'],
                    ['count' => $pendingPayment, 'label' => 'Chờ thanh toán', 'icon' => 'clock', 'color' => 'warning'],
                    ['count' => $orderDelivered, 'label' => 'Đã giao', 'icon' => 'box-seam', 'color' => 'success'],
                ];
            @endphp

            @foreach ($statuses as $item)
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center justify-content-between">
                        <div>
                            <div class="fs-3 fw-bold">{{ $item['count'] }}</div>
                            <small class="text-muted">{{ $item['label'] }}</small>
                        </div>
                        <div class="bg-light p-3 rounded-circle">
                            <i class="bi bi-{{ $item['icon'] }} fs-4 text-{{ $item['color'] }}"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <form method="GET" action="{{ route('orders.index') }}" class="row g-2 mb-4 align-items-center">
            <div class="col-md-2">
                <input type="text"
                       name="order_code"
                       value="{{ request('order_code') }}"
                       class="form-control"
                       placeholder="Mã đơn hàng">
            </div>

            <div class="col-md-2">
                <input type="text"
                       name="customer"
                       value="{{ request('customer') }}"
                       class="form-control"
                       placeholder="Khách hàng">
            </div>

            <div class="col-md-2">
                <select name="payment_status" class="form-select">
                    <option value="">-- Trạng thái thanh toán --</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Đang chờ thanh toán</option>
                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Thanh toán lỗi</option>
                    <option value="cancelled" {{ request('payment_status') == 'cancelled' ? 'selected' : '' }}>Thanh toán đã hủy</option>
                    <option value="expired" {{ request('payment_status') == 'expired' ? 'selected' : '' }}>Thanh toán hết hạn</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="order_status" class="form-select">
                    <option value="">-- Trạng thái đơn hàng --</option>
                    <option value="pending" {{ request('order_status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="processing" {{ request('order_status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                    <option value="shipped" {{ request('order_status') == 'shipped' ? 'selected' : '' }}>Đang giao hàng</option>
                    <option value="delivered" {{ request('order_status') == 'delivered' ? 'selected' : '' }}>Đã giao</option>
                    <option value="cancelled" {{ request('order_status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="waiting_for_cancellation" {{ request('order_status') == 'waiting_for_cancellation' ? 'selected' : '' }}>Chờ duyệt hủy</option>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">Tìm kiếm</button>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Đặt lại</a>
            </div>
        </form>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="table-responsive p-3">
                <table class="table table-hover table-striped align-middle rounded orders-table">
                    <thead class="table-light text-uppercase text-center small">
                        <tr>
                            <th class="col-code">Mã đơn</th>
                            <th class="col-date">Ngày tạo</th>
                            <th class="col-customer">Khách hàng</th>
                            <th class="col-total">Tổng tiền</th>
                            <th class="col-payment">TT thanh toán</th>
                            <th class="col-status">Trạng thái</th>
                            <th class="col-ghn">GHN</th>
                            <th class="col-action">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $paymentLabels = [
                                    'paid' => ['label' => 'Đã thanh toán', 'color' => 'bg-success'],
                                    'unpaid' => ['label' => 'Chưa thanh toán', 'color' => 'bg-secondary'],
                                    'pending' => ['label' => 'Đang chờ thanh toán', 'color' => 'bg-warning text-dark'],
                                    'failed' => ['label' => 'Thanh toán thất bại', 'color' => 'bg-danger'],
                                    'cancelled' => ['label' => 'Thanh toán đã hủy', 'color' => 'bg-dark'],
                                    'expired' => ['label' => 'Thanh toán hết hạn', 'color' => 'bg-secondary'],
                                ];

                                $paymentInfo = $paymentLabels[$order->payment_status] ?? [
                                    'label' => ucfirst((string) $order->payment_status),
                                    'color' => 'bg-light text-dark',
                                ];

                                $statusLabels = [
                                    'pending' => ['label' => 'Chờ xử lý', 'color' => 'bg-warning text-dark'],
                                    'processing' => ['label' => 'Đang xử lý', 'color' => 'bg-info'],
                                    'shipped' => ['label' => 'Đang giao hàng', 'color' => 'bg-primary'],
                                    'delivered' => ['label' => 'Đã giao', 'color' => 'bg-success'],
                                    'cancelled' => ['label' => 'Đã hủy', 'color' => 'bg-danger'],
                                    'waiting_for_cancellation' => ['label' => 'Chờ duyệt hủy', 'color' => 'bg-dark'],
                                ];

                                $statusInfo = $statusLabels[$order->order_status] ?? [
                                    'label' => ucfirst((string) $order->order_status),
                                    'color' => 'bg-light text-dark',
                                ];

                                $refundStatus = $order->refund_status ?? 'none';
                                $refundedAmount = (float) ($order->refunded_amount ?? 0);
                                $netPaidAmount = max((float) $order->total_price - $refundedAmount, 0);
                                $refundableAmount = (float) ($order->refundable_amount ?? 0);

                                $refundInfo = match ($refundStatus) {
                                    'requested' => [
                                        'label' => 'Chờ hoàn tiền',
                                        'class' => 'bg-warning text-dark',
                                    ],
                                    'partially_refunded' => [
                                        'label' => 'Hoàn một phần',
                                        'class' => 'bg-info',
                                    ],
                                    'refunded' => [
                                        'label' => 'Đã hoàn tiền',
                                        'class' => 'bg-danger',
                                    ],
                                    'rejected' => [
                                        'label' => 'Từ chối hoàn',
                                        'class' => 'bg-secondary',
                                    ],
                                    default => null,
                                };

                                $customerEmail = $order->user->email ?? $order->email ?? 'N/A';
                            @endphp

                            <tr class="text-center">
                                <td class="fw-semibold">
                                    <span class="order-code-text" title="#{{ $order->order_code }}">
                                        #{{ $order->order_code }}
                                    </span>
                                </td>

                                <td>
                                    {{ $order->created_at->format('d/m/Y') }}
                                </td>

                                <td class="text-danger fw-semibold">
                                    <span class="customer-email" title="{{ $customerEmail }}">
                                        {{ $customerEmail }}
                                    </span>
                                </td>

                                <td class="fw-medium">
                                    <div>
                                        {{ number_format($order->total_price, 0, ',', '.') }}₫
                                    </div>

                                    @if ($refundedAmount > 0)
                                        <div class="refund-money-line text-danger">
                                            -{{ number_format($refundedAmount, 0, ',', '.') }}₫
                                        </div>

                                        <div class="refund-money-line text-success fw-semibold">
                                            Thực thu: {{ number_format($netPaidAmount, 0, ',', '.') }}₫
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge rounded-pill px-3 py-2 badge-order {{ $paymentInfo['color'] }}">
                                        {{ $paymentInfo['label'] }}
                                    </span>

                                    @if ($refundInfo)
                                        <div class="mt-1">
                                            <span class="badge rounded-pill refund-status-badge {{ $refundInfo['class'] }}">
                                                {{ $refundInfo['label'] }}
                                            </span>
                                        </div>
                                    @endif

                                    @if ($refundedAmount > 0)
                                        <div class="refund-money-line text-danger fw-semibold mt-1">
                                            Đã hoàn: {{ number_format($refundedAmount, 0, ',', '.') }}₫
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge rounded-pill px-3 py-2 badge-order {{ $statusInfo['color'] }}">
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </td>

                                <td>
                                    @if ($order->ghn_order_code)
                                        <div class="ghn-info">
                                            <div class="ghn-code-text" title="{{ $order->ghn_order_code }}">
                                                <strong>Mã:</strong>
                                                <span class="text-primary fw-semibold">{{ $order->ghn_order_code }}</span>
                                            </div>

                                            <div class="mt-1">
                                                <span class="badge {{ $order->ghn_status_group_badge }}"
                                                      title="{{ $order->ghn_status_group }}">
                                                    {{ $order->ghn_status_group }}
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-dark">Chưa gửi GHN</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="order-actions">
                                        <a href="{{ route('orders.show', $order->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Xem chi tiết">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>

                                        @if (
                                            !$order->ghn_order_code
                                            && !in_array($order->order_status, ['delivered', 'cancelled'], true)
                                            && ($order->payment_method === 'cod' || $order->payment_status === 'paid')
                                        )
                                            <form action="{{ route('orders.ghn.create', $order) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-success"
                                                        title="Tạo vận đơn GHN"
                                                        onclick="return confirm('Tạo vận đơn GHN cho đơn này?')">
                                                    <i class="bi bi-truck"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($order->ghn_order_code)
                                            <form action="{{ route('orders.ghn.sync', $order) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-info"
                                                        title="Đồng bộ GHN">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            </form>

                                            <a href="{{ route('orders.ghn.print', $order) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-dark"
                                               title="In vận đơn GHN">
                                                <i class="bi bi-printer"></i>
                                            </a>

                                            @if (!in_array($order->ghn_status, ['delivered', 'cancel', 'returned', 'lost', 'damage'], true))
                                                <form action="{{ route('orders.ghn.cancel', $order) }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Hủy vận đơn GHN"
                                                            onclick="return confirm('Hủy vận đơn GHN cho đơn này?')">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        @if ($order->order_status === 'cancelled')
                                            <button onclick="showDeleteModal({{ $order->id }})"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Xóa">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Không có đơn hàng nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $orders->withQueryString()->links() }}
    </div>

    @push('scripts')
        <script>
            function showDeleteModal(orderId) {
                const form = document.getElementById('formDeleteOrder');
                form.action = '/orders/' + orderId;
                new bootstrap.Modal(document.getElementById('modalDeleteOrder')).show();
            }
        </script>
    @endpush

    <div class="modal fade" id="modalDeleteOrder" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formDeleteOrder" method="POST">
                @csrf
                @method('DELETE')

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Xóa đơn hàng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa đơn hàng này không?</p>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xóa</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
