@extends('admin.layouts.layout')

@section('content')
<div class="container-xxl">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    @endif
    <div class="row">
        <div class="col-xl-12">

            <div class="card">

              
                <div class="card-header d-flex justify-content-between align-items-center gap-1">

                    <h4 class="card-title flex-grow-1">Danh sách Voucher</h4>

                  
                    <a href="{{ route('vouchers.create') }}" class="btn btn-sm btn-primary">
                        Thêm Voucher
                    </a>

                    <a href="{{ route('vouchers.trashed') }}" class="btn btn-sm btn-outline-danger">
                        Đã xóa
                    </a>

                 
                    <form action="{{ route('vouchers.index') }}" method="GET">
                        <div class="search-bar">
                            <span><i class="bx bx-search-alt"></i></span>
                            <input name="search" type="search"
                                   class="form-control"
                                   placeholder="Tìm kiếm voucher..."
                                   value="{{ request('search') }}">
                        </div>
                    </form>

                </div>

           
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover table-centered">

                        <thead class="bg-light-subtle">
                            <tr>
                                <th style="width: 20px;">
                                    <input type="checkbox" class="form-check-input">
                                </th>
                                <th>Mã</th>
                                <th>Loại</th>
                                <th>Giá trị</th>
                                <th>Giảm tối đa</th>
                                <th>Thời gian</th>
                                <th>Số lượng</th>
                                <th>Số lần sử dụng tối đa cho mỗi tài khoản</th>
                                <th>Số lần sử dụng tối đa cho mỗi đơn hàng</th>
                                 <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse($vouchers as $v)
                            <tr>

                                <td>
                                    <input type="checkbox" class="form-check-input">
                                </td>

                             
                                <td class="fw-medium">{{ $v->code }}</td>

                           
                                <td>
                                    @if($v->type == 'percent')
                                        <span class="badge bg-info">%</span>
                                    @else
                                        <span class="badge bg-warning text-dark">₫</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $v->type == 'percent'
                                        ? $v->value.'%'
                                        : number_format($v->value,0,',','.') . 'đ' }}
                                </td>

                               
                                <td>
                                    {{ $v->max_discount
                                        ? number_format($v->max_discount,0,',','.') . 'đ'
                                        : '-' }}
                                </td>

                                <td style="font-size:13px">
                                    {{ $v->start_date->format('d/m/Y') }} <br>
                                    <span class="text-muted">
                                        → {{ $v->end_date->format('d/m/Y') }}
                                    </span>
                                    @if($v->actual_status == 'active')
                                        <br>
                                        <span class="badge bg-danger countdown-timer" 
                                              data-end-date="{{ $v->end_date->format('Y-m-d H:i:s') }}">
                                            Đang tải...
                                        </span>
                                    @endif
                                </td>

                                <td>{{ $v->quantity === 0 ? 'Vô hạn' : $v->quantity }}</td>

                                <td>{{ $v->max_uses_per_user }}</td>

                                <td>{{ $v->max_uses_per_order }}</td>

                                <td>
                                    @if($v->actual_status == 'active')
                                        <span class="badge bg-success">Hoạt động</span>
                                    @elseif($v->actual_status == 'inactive')
                                        <span class="badge bg-warning">Tắt</span>
                                    @else
                                        <span class="badge bg-danger">Hết hạn</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('vouchers.edit',$v) }}"
                                           class="btn btn-soft-primary btn-sm" title="Sửa voucher">
                                            <iconify-icon icon="solar:pen-2-broken"
                                                class="align-middle fs-18"></iconify-icon>
                                        </a>

                                        <form action="{{ route('vouchers.destroy',$v) }}"
                                              method="POST">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="btn btn-soft-danger btn-sm"
                                                onclick="return confirm('Bạn có chắc muốn xoá không?')"
                                                @if($v->hasAppliedOrders()) disabled title="Voucher đã áp dụng cho đơn hàng, không thể xóa" @endif>
                                                <iconify-icon icon="solar:trash-bin-minimalistic-2-broken"
                                                    class="align-middle fs-18"></iconify-icon>
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">
                                    Không có voucher
                                </td>
                            </tr>
                        @endforelse
                        </tbody>

                    </table>
                </div>

                <!-- FOOTER -->
                <div class="card-footer border-top d-flex justify-content-between">
                    <div class="text-muted">
                        Hiển thị {{ $vouchers->count() }} / {{ $vouchers->total() }} voucher
                    </div>

                    {{ $vouchers->links() }}
                </div>

            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Countdown timer cho các voucher đang hoạt động
    const countdownElements = document.querySelectorAll('.countdown-timer');
    
    countdownElements.forEach(function(element) {
        const endDate = new Date(element.getAttribute('data-end-date')).getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = endDate - now;
            
            if (distance > 0) {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                element.textContent = `${days} ngày ${hours}:${minutes}:${seconds}`;
            } else {
                element.textContent = 'Đã hết hạn';
                element.classList.remove('bg-danger');
                element.classList.add('bg-secondary');
            }
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
});
</script>
@endsection