@extends('admin.layouts.layout')

@section('content')
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
            <h3 class="fs-4 fw-semibold mb-1">Yêu cầu hoàn tiền</h3>
            <div class="text-muted">Duyệt hoàn tiền demo vào ví người dùng cho đơn VNPay/COD đã thanh toán.</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="text-muted small">Chờ duyệt</div>
                <div class="fs-3 fw-bold text-warning">{{ $counts['pending'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="text-muted small">Đã hoàn</div>
                <div class="fs-3 fw-bold text-success">{{ $counts['approved'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="text-muted small">Đã từ chối</div>
                <div class="fs-3 fw-bold text-danger">{{ $counts['rejected'] }}</div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.refunds.index') }}" class="row g-2 mb-4 align-items-center">
        <div class="col-md-4">
            <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="Tìm mã đơn, email, tên khách">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã hoàn</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
            </select>
        </div>
        <div class="col-md-5 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">Tìm kiếm</button>
            <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-secondary">Đặt lại</a>
        </div>
    </form>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive p-3">
            <table class="table table-hover table-striped align-middle text-nowrap">
                <thead class="table-light text-center text-uppercase small">
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Loại hoàn</th>
                        <th>Số tiền yêu cầu</th>
                        <th>Bằng chứng</th>
                        <th>Trạng thái</th>
                        <th>Ngày gửi</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($refundRequests as $refund)
                        <tr class="text-center">
                            <td class="fw-semibold">{{ optional($refund->order)->order_code ?: '-' }}</td>
                            <td>{{ optional($refund->user)->email ?: '-' }}</td>
                            <td>{{ $refund->type_label }}</td>
                            <td class="fw-bold text-danger">{{ number_format($refund->requested_amount, 0, ',', '.') }} đ</td>
                            <td>{{ $refund->evidences->count() }} file</td>
                            <td><span class="badge bg-{{ $refund->status_badge }}">{{ $refund->status_label }}</span></td>
                            <td>{{ $refund->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.refunds.show', $refund->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Chưa có yêu cầu hoàn tiền nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $refundRequests->links() }}
    </div>
</div>
@endsection
