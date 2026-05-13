@extends('admin.layouts.layout')

@section('content')
<style>
    .refund-evidence-img {
        width: 100%;
        max-height: 360px;
        object-fit: contain;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
    }

    .refund-evidence-video {
        width: 100%;
        max-height: 360px;
        border-radius: 14px;
        background: #000;
    }
</style>

<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="fs-4 fw-semibold mb-1">Chi tiết yêu cầu hoàn tiền</h3>
            <div class="text-muted">Mã đơn: <strong>{{ optional($refundRequest->order)->order_code }}</strong></div>
        </div>
        <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header fw-bold">Thông tin yêu cầu</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Khách hàng:</strong> {{ optional($refundRequest->user)->email }}</div>
                        <div class="col-md-6"><strong>Trạng thái:</strong> <span class="badge bg-{{ $refundRequest->status_badge }}">{{ $refundRequest->status_label }}</span></div>
                        <div class="col-md-6"><strong>Loại hoàn:</strong> {{ $refundRequest->type_label }}</div>
                        <div class="col-md-6"><strong>Số tiền yêu cầu:</strong> <span class="text-danger fw-bold">{{ number_format($refundRequest->requested_amount, 0, ',', '.') }} đ</span></div>
                        <div class="col-md-6"><strong>Số tiền đã duyệt:</strong> {{ $refundRequest->approved_amount ? number_format($refundRequest->approved_amount, 0, ',', '.') . ' đ' : '-' }}</div>
                        <div class="col-md-6"><strong>Ngày gửi:</strong> {{ $refundRequest->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <hr>

                    <div>
                        <strong>Lý do hoàn tiền:</strong>
                        <div class="mt-2 p-3 bg-light rounded-3">{{ $refundRequest->reason }}</div>
                    </div>

                    @if($refundRequest->admin_note)
                        <div class="mt-3">
                            <strong>Ghi chú admin:</strong>
                            <div class="mt-2 p-3 bg-light rounded-3">{{ $refundRequest->admin_note }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header fw-bold">Sản phẩm yêu cầu hoàn</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Phân loại</th>
                                    <th class="text-center">SL hoàn</th>
                                    <th class="text-center">SL đã nhập kho</th>
                                    <th class="text-end">Đơn giá hoàn</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($refundRequest->items as $item)
                                    <tr>
                                        <td class="fw-semibold">{{ $item->product_name_snapshot }}</td>
                                        <td>{{ $item->variant_snapshot ?: '-' }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-center">{{ $item->restocked_quantity ?? 0 }}</td>
                                        <td class="text-end">{{ number_format($item->unit_amount, 0, ',', '.') }} đ</td>
                                        <td class="text-end fw-bold text-danger">{{ number_format($item->line_amount, 0, ',', '.') }} đ</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Yêu cầu hoàn toàn bộ số tiền còn lại của đơn.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">Bằng chứng ảnh/video</div>

                <div class="card-body">
                    @if($refundRequest->evidences->isEmpty())
                        <div class="text-muted">Khách hàng chưa tải bằng chứng.</div>
                    @else
                        <div class="row g-3">
                            @foreach($refundRequest->evidences as $evidence)
                                @php
                                    $filePath = ltrim((string) $evidence->file_path, '/');

                                    if (str_starts_with($filePath, 'storage/')) {
                                        $fileUrl = asset($filePath);
                                    } else {
                                        $fileUrl = asset('storage/' . $filePath);
                                    }

                                    $isVideo = str_starts_with((string) $evidence->mime_type, 'video/')
                                        || $evidence->file_type === 'video';
                                @endphp

                                <div class="col-md-6 col-lg-4">
                                    <div class="border rounded-4 p-3 h-100 bg-white">
                                        @if($isVideo)
                                            <video controls
                                                class="w-100 rounded-3 border"
                                                style="height: 260px; object-fit: contain; background: #000;">
                                                <source src="{{ $fileUrl }}" type="{{ $evidence->mime_type }}">
                                                Trình duyệt không hỗ trợ xem video.
                                            </video>
                                        @else
                                            <a href="{{ $fileUrl }}" target="_blank">
                                                <img src="{{ $fileUrl }}"
                                                    alt="Bằng chứng hoàn tiền"
                                                    class="img-fluid rounded-3 border"
                                                    style="width: 100%; height: 260px; object-fit: contain; background: #f8fafc;"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <div class="alert alert-warning mt-2 mb-0" style="display:none;">
                                                    Không tải được ảnh. Bấm “Mở file” để kiểm tra đường dẫn.
                                                </div>
                                            </a>
                                        @endif

                                        <a href="{{ $fileUrl }}"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary rounded-pill mt-2">
                                            Mở file
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header fw-bold">Thông tin đơn hàng</div>
                <div class="card-body">
                    @php($order = $refundRequest->order)
                    <p><strong>Mã đơn:</strong> {{ $order->order_code }}</p>
                    <p><strong>Tổng khách đã thanh toán:</strong> {{ number_format($order->total_price, 0, ',', '.') }} đ</p>
                    <p><strong>Đã hoàn sản phẩm:</strong> {{ number_format($order->refunded_amount, 0, ',', '.') }} đ</p>
                    <p><strong>Còn có thể hoàn sản phẩm:</strong> <span class="text-danger fw-bold">{{ number_format($order->refundable_amount, 0, ',', '.') }} đ</span></p>
                    <p><strong>Thanh toán:</strong> {{ strtoupper($order->payment_method) }} - {{ $order->payment_status_label }}</p>
                    <p><strong>Cơ chế hoàn:</strong> Ví demo nội bộ website</p>
                    <p><strong>Trạng thái đơn:</strong> {{ $order->order_status_label }}</p>
                    <p><strong>Trạng thái hoàn:</strong> {{ $order->refund_status_label }}</p>
                    <p><strong>Nhập lại kho:</strong>
                        @if($refundRequest->restocked_at)
                            <span class="badge bg-success">Đã nhập {{ $refundRequest->restocked_at->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="badge bg-secondary">Chưa nhập kho</span>
                        @endif
                    </p>
                </div>
            </div>


            @if($refundRequest->status === 'approved' && !$refundRequest->restocked_at && $refundRequest->items->isNotEmpty())
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header fw-bold text-primary">Nhập lại kho hàng hoàn</div>
                    <div class="card-body">
                        <div class="alert alert-warning small">
                            Chỉ bấm nút này khi shop đã nhận hàng trả về và hàng còn đủ điều kiện bán lại.
                            Duyệt hoàn tiền không tự động cộng kho.
                        </div>

                        <form method="POST" action="{{ route('admin.refunds.restock', $refundRequest->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Ghi chú nhập kho</label>
                                <textarea name="restock_note" rows="3" class="form-control" placeholder="Ví dụ: Đã nhận hàng hoàn, sản phẩm còn nguyên vẹn..."></textarea>
                            </div>
                            <button type="submit"
                                    class="btn btn-primary w-100"
                                    onclick="return confirm('Xác nhận đã nhận hàng trả và nhập lại kho?')">
                                Xác nhận nhập lại kho
                            </button>
                        </form>
                    </div>
                </div>
            @elseif($refundRequest->restocked_at)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header fw-bold text-success">Đã nhập lại kho</div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Thời gian:</strong> {{ $refundRequest->restocked_at->format('d/m/Y H:i') }}</p>
                        @if($refundRequest->restock_note)
                            <p class="mb-0"><strong>Ghi chú:</strong> {{ $refundRequest->restock_note }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if($refundRequest->status === 'pending')
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header fw-bold text-success">Duyệt hoàn vào ví demo</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.refunds.approve', $refundRequest->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Ghi chú admin</label>
                                <textarea name="admin_note" rows="3" class="form-control" placeholder="Ghi chú nội bộ hoặc thông báo cho khách..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Duyệt yêu cầu và cộng tiền vào ví demo?')">
                                Duyệt hoàn vào ví demo
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header fw-bold text-danger">Từ chối yêu cầu</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.refunds.reject', $refundRequest->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Lý do từ chối</label>
                                <textarea name="admin_note" rows="4" class="form-control" required placeholder="Nhập lý do từ chối hoàn tiền..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Từ chối yêu cầu hoàn tiền này?')">
                                Từ chối
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
