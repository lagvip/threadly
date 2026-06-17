@extends('admin.layouts.layout')

@section('content')
    <style>
        html,
        body {
            overflow-x: hidden;
        }

        .orders-index-page,
        .orders-index-page * {
            box-sizing: border-box;
        }

        .orders-index-page {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            color: #334155;
        }

        .orders-index-page .orders-container {
            width: 100%;
            max-width: 100%;
            padding-left: 12px;
            padding-right: 12px;
        }

        .orders-index-page .card {
            max-width: 100%;
        }

        .orders-filter .form-control,
        .orders-filter .form-select,
        .orders-filter .btn {
            height: 38px;
            font-size: 13px;
            border-radius: 8px;
        }

        .orders-table-card {
            max-width: 100%;
            overflow: hidden;
        }

        .orders-table-box {
            width: 100%;
            max-width: 100%;
            overflow: visible !important;
            padding: 14px;
        }

        .orders-table {
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
            margin-bottom: 0;
            font-size: 12px;
        }

        .orders-table th,
        .orders-table td {
            vertical-align: middle;
            border-color: #eef2f7;
            padding: 10px 6px;
            line-height: 1.35;
            white-space: normal;
            word-break: break-word;
        }

        .orders-table thead th {
            background: #f1f5f9;
            color: #1f2937;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
            white-space: normal;
        }

        .orders-table tbody td {
            text-align: center;
        }

        .orders-table .col-code {
            width: 12%;
        }

        .orders-table .col-date {
            width: 8%;
        }

        .orders-table .col-customer {
            width: 17%;
        }

        .orders-table .col-total {
            width: 12%;
        }

        .orders-table .col-payment {
            width: 15%;
        }

        .orders-table .col-status {
            width: 11%;
        }

        .orders-table .col-ghn {
            width: 13%;
        }

        .orders-table .col-action {
            width: 12%;
        }

        .order-code-text,
        .customer-email,
        .ghn-code-text {
            display: block;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .order-code-text {
            color: #475569;
            font-weight: 700;
        }

        .customer-email {
            color: #ef4444;
            font-weight: 700;
            font-size: 11.5px;
        }

        .order-money {
            font-size: 12px;
            line-height: 1.35;
            font-variant-numeric: tabular-nums;
        }

        .refund-money-line {
            font-size: 10.5px;
            line-height: 1.35;
            margin-top: 2px;
            font-variant-numeric: tabular-nums;
        }

        .orders-table .badge {
            max-width: 100%;
            white-space: normal;
            line-height: 1.25;
            text-align: center;
        }

        .badge-order {
            display: inline-block;
            width: auto;
            max-width: 100%;
            padding: 6px 10px !important;
            font-size: 10.5px;
            font-weight: 700;
            border-radius: 999px;
        }

        .refund-status-badge {
            display: inline-block;
            padding: 4px 7px !important;
            font-size: 10px;
            font-weight: 700;
            border-radius: 999px;
        }

        .ghn-info {
            width: 100%;
            max-width: 100%;
            line-height: 1.3;
            font-size: 11px;
        }

        .ghn-info .badge {
            display: inline-block;
            padding: 4px 7px;
            font-size: 10px;
            font-weight: 700;
            border-radius: 5px;
        }

        .order-actions {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            flex-wrap: wrap;
            white-space: normal;
        }

        .order-actions form {
            display: inline-flex;
            margin: 0;
        }

        .order-actions .btn {
            width: 25px;
            height: 25px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 7px;
        }

        .order-actions .btn i {
            font-size: 11px;
            line-height: 1;
        }

        .summary-card {
            min-height: 92px;
        }

        .summary-card .summary-number {
            font-size: 24px;
            line-height: 1;
        }

        .summary-card small {
            font-size: 12px;
        }

        @media (max-width: 1500px) {
            .orders-index-page .orders-container {
                padding-left: 10px;
                padding-right: 10px;
            }

            .orders-table-box {
                padding: 12px;
            }

            .orders-table {
                font-size: 11.5px;
            }

            .orders-table th,
            .orders-table td {
                padding: 9px 5px;
            }

            .orders-table thead th {
                font-size: 10.5px;
            }

            .badge-order {
                font-size: 10px;
                padding: 5px 8px !important;
            }

            .refund-status-badge {
                font-size: 9.5px;
                padding: 3px 6px !important;
            }

            .customer-email {
                font-size: 11px;
            }

            .order-actions {
                gap: 3px;
            }

            .order-actions .btn {
                width: 24px;
                height: 24px;
            }

            .order-actions .btn i {
                font-size: 10.5px;
            }
        }

        @media (max-width: 1300px) {
            .orders-table {
                font-size: 10.8px;
            }

            .orders-table th,
            .orders-table td {
                padding: 8px 4px;
            }

            .orders-table thead th {
                font-size: 10px;
            }

            .orders-table .col-code {
                width: 11%;
            }

            .orders-table .col-date {
                width: 8%;
            }

            .orders-table .col-customer {
                width: 16%;
            }

            .orders-table .col-total {
                width: 12%;
            }

            .orders-table .col-payment {
                width: 15%;
            }

            .orders-table .col-status {
                width: 11%;
            }

            .orders-table .col-ghn {
                width: 13%;
            }

            .orders-table .col-action {
                width: 14%;
            }

            .badge-order {
                font-size: 9.5px;
                padding: 5px 7px !important;
            }

            .refund-money-line {
                font-size: 9.5px;
            }

            .ghn-info {
                font-size: 10px;
            }

            .ghn-info .badge {
                font-size: 9.5px;
                padding: 3px 6px;
            }
        }
    </style>

    <div class="orders-index-page">
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

        <div class="orders-container py-4">
            <h3 class="fs-5 fw-semibold mb-4">Tổng quan đơn hàng</h3>

            <div class="row g-3 mb-4">
                @php
                    $statuses = [
                        ['count' => $orderCancel, 'label' => 'Đã hủy', 'icon' => 'cart-x', 'color' => 'danger'],
                        ['count' => $orderDelivering, 'label' => 'Đang giao', 'icon' => 'truck', 'color' => 'info'],
                        ['count' => $pendingPayment, 'label' => 'Chờ thanh toán', 'icon' => 'clock', 'color' => 'warning'],
                        ['count' => $orderDelivered, 'label' => 'Đã giao', 'icon' => 'box-seam', 'color' => 'success'],
                    ];
                @endphp

                @foreach ($statuses as $item)
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center justify-content-between summary-card">
                            <div>
                                <div class="summary-number fw-bold">{{ $item['count'] }}</div>
                                <small class="text-muted">{{ $item['label'] }}</small>
                            </div>

                            <div class="bg-light p-3 rounded-circle">
                                <i class="bi bi-{{ $item['icon'] }} fs-5 text-{{ $item['color'] }}"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="GET" action="{{ route('orders.index') }}" class="row g-2 mb-4 align-items-center orders-filter">
                <div class="col-xl-2 col-lg-4 col-md-6">
                    <input type="text"
                           name="order_code"
                           value="{{ request('order_code') }}"
                           class="form-control"
                           placeholder="Mã đơn hàng">
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6">
                    <input type="text"
                           name="customer"
                           value="{{ request('customer') }}"
                           class="form-control"
                           placeholder="Khách hàng">
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6">
                    <select name="payment_status" class="form-select">
                        <option value="">-- Trạng thái thanh toán --</option>
                        @foreach($paymentStatusOptions as $value => $option)
                            <option value="{{ $value }}" {{ request('payment_status') == $value ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6">
                    <select name="order_status" class="form-select">
                        <option value="">-- Trạng thái đơn hàng --</option>
                        @foreach($orderStatusOptions as $value => $option)
                            <option value="{{ $value }}" {{ request('order_status') == $value ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-4 col-lg-8 col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Tìm kiếm</button>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary px-3">Đặt lại</a>
                </div>
            </form>

            <div class="card border-0 shadow-sm rounded-4 orders-table-card">
                <div class="orders-table-box">
                    <table class="table table-hover table-striped align-middle rounded orders-table">
                        <thead>
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
                                    $paymentInfo = $paymentStatusOptions[$order->payment_status] ?? [
                                        'label' => $order->payment_status_label,
                                        'color' => 'bg-'.$order->payment_status_badge,
                                    ];
                                    $statusInfo = $orderStatusOptions[$order->order_status] ?? [
                                        'label' => $order->order_status_label,
                                        'color' => 'bg-'.$order->order_status_badge,
                                    ];
                                    $refundStatus = $order->refund_status ?? 'none';
                                    $refundedAmount = (float) ($order->refunded_amount ?? 0);
                                    $netPaidAmount = max((float) $order->total_price - $refundedAmount, 0);
                                    $refundInfo = $refundStatusOptions[$refundStatus] ?? null;

                                    $customerEmail = $order->user->email ?? $order->email ?? 'Kh�ng c�';
                                @endphp

                                <tr>
                                    <td class="fw-semibold">
                                        <span class="order-code-text" title="#{{ $order->order_code }}">
                                            #{{ $order->order_code }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $order->created_at->format('d/m/Y') }}
                                    </td>

                                    <td>
                                        <span class="customer-email" title="{{ $customerEmail }}">
                                            {{ $customerEmail }}
                                        </span>
                                    </td>

                                    <td class="fw-medium">
                                        <div class="order-money">
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
                                        <span class="badge badge-order {{ $paymentInfo['color'] }}">
                                            {{ $paymentInfo['label'] }}
                                        </span>

                                        @if ($refundInfo)
                                            <div class="mt-1">
                                                <span class="badge refund-status-badge {{ $refundInfo['class'] }}">
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
                                        <span class="badge badge-order {{ $statusInfo['color'] }}">
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
                                                && !in_array($order->order_status, [$deliveredOrderStatus, $cancelledOrderStatus], true)
                                                && ($order->payment_method === $codPaymentMethod || $order->payment_status === $paidPaymentStatus)
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

                                                @if (!in_array($order->ghn_status, $ghnTerminalStatuses, true))
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

                                            @if ($order->order_status === $cancelledOrderStatus)
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
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        Không có đơn hàng nào
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                {{ $orders->withQueryString()->links() }}
            </div>
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
    </div>
@endsection
