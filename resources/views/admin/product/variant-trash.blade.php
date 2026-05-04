@extends('admin.layouts.layout')

@section('content')
<div class="container-xxl">
    <form action="{{ route('product.variant.restore') }}" method="POST" id="bulkRestoreForm">
        @csrf
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Biến Thể Đã Xoá</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover table-centered">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="checkAll">
                                        </div>
                                    </th>
                                    <th>STT</th>
                                    <th>Sản phẩm</th>
                                    <th>Màu</th>
                                    <th>Kích cỡ</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Ảnh</th>
                                    <th>Ngày Xoá</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trashedVariants as $variant)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="ids[]" value="{{ $variant->id }}" class="form-check-input checkbox-item">
                                        </td>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $variant->product->name ?? 'Không có' }}</td>
                                        <td>{{ $variant->color->name ?? 'Không có' }}</td>
                                        <td>{{ $variant->size->name ?? 'Không có' }}</td>
                                        <td>{{ number_format($variant->price) }} đ</td>
                                        <td>{{ $variant->quantity }}</td>
                                        <td>
                                            @if($variant->image)
                                                <img src="{{ asset('storage/'.$variant->image) }}" alt="Biến thể" width="50">
                                            @else
                                                Không có
                                            @endif
                                        </td>
                                        <td>{{ $variant->deleted_at }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <form action="{{ route('product.variant.restore') }}" method="POST" style="display:inline">
                                                    @csrf
                                                    <input type="hidden" name="ids[]" value="{{ $variant->id }}">
                                                    <button type="submit" class="btn btn-soft-success btn-sm" onclick="return confirm('Khôi phục biến thể này?')">
                                                        <iconify-icon icon="solar:refresh-circle-broken" class="align-middle fs-18"></iconify-icon>
                                                    </button>
                                                </form>

                                                <form action="{{ route('product.variant.forceDelete') }}" method="POST" style="display:inline">
                                                    @csrf
                                                    <input type="hidden" name="ids[]" value="{{ $variant->id }}">
                                                    <button type="submit" class="btn btn-soft-danger btn-sm" onclick="return confirm('Xóa vĩnh viễn biến thể này?')">
                                                        <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10">Không tìm thấy biến thể đã xoá</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer border-top">
                        <button type="submit" class="btn btn-primary me-2" onclick="return confirm('Khôi phục các mục đã chọn?')">Khôi phục đã chọn</button>

                        <form action="{{ route('product.variant.forceDelete') }}" method="POST" style="display:inline" id="bulkDeleteForm">
                            @csrf
                            <input type="hidden" name="ids[]" id="bulkDeleteIds">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Xóa vĩnh viễn các mục đã chọn?')">Xóa vĩnh viễn đã chọn</button>
                        </form>

                        <a href="{{ route('product.listProduct') }}" class="btn btn-secondary ms-2">Quay lại</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Check all
    document.getElementById('checkAll').addEventListener('change', function () {
        document.querySelectorAll('.checkbox-item').forEach(cb => cb.checked = this.checked);
    });

    // Gửi danh sách ids cho bulk delete
    document.getElementById('bulkDeleteForm').addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.checkbox-item:checked');
        if (checked.length === 0) {
            e.preventDefault();
            alert('Chưa chọn biến thể nào');
            return;
        }

        let input = '';
        checked.forEach(cb => {
            input += `<input type="hidden" name="ids[]" value="${cb.value}">`;
        });
        this.insertAdjacentHTML('beforeend', input);
    });
</script>
@endsection
