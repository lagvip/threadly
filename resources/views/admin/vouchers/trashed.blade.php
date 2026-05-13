@extends('admin.layouts.layout')

@section('content')
<div class="container-xxl">
    <div class="row">
        <div class="col-xl-12">

            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center gap-1">
                    <h4 class="card-title flex-grow-1">Danh sách Voucher đã xóa</h4>

                    <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-primary">
                        Quay lại
                    </a>
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
                                <th>Sử dụng/người dùng</th>
                                <th>Sử dụng/Đơn</th>
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
                                </td>

                                <td>{{ $v->quantity }}</td>

                                <td>{{ $v->max_uses_per_user ?? 1 }}</td>

                                <td>{{ $v->max_uses_per_order ?? 1 }}</td>

                                <td>
                                    <div class="d-flex gap-2">
                                     
                                        
                                        <form action="{{ route('vouchers.restore', $v) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                onclick="return confirm('Bạn có chắc muốn khôi phục voucher này không?')"
                                                class="btn btn-soft-success btn-sm">
                                                <iconify-icon icon="solar:refresh-circle-broken"
                                                    class="align-middle fs-18"></iconify-icon>
                                                Khôi phục
                                            </button>
                                        </form>

                                        
                                        <form action="{{ route('vouchers.forceDelete', $v) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                onclick="return confirm('Bạn có chắc muốn xóa vĩnh viễn voucher này không? Không thể khôi phục lại!')"
                                                class="btn btn-soft-danger btn-sm">
                                                <iconify-icon icon="solar:trash-bin-minimalistic-2-broken"
                                                    class="align-middle fs-18"></iconify-icon>
                                                Xóa vĩnh viễn
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    Không có voucher đã xóa
                                </td>
                            </tr>
                        @endforelse
                        </tbody>

                    </table>
                </div>

              
                <div class="card-footer border-top d-flex justify-content-between">
                    <div class="text-muted">
                        Hiển thị {{ $vouchers->count() }} / {{ $vouchers->total() }} voucher đã xóa
                    </div>

                    {{ $vouchers->links() }}
                </div>

            </div>

        </div>
    </div>
</div>
@endsection