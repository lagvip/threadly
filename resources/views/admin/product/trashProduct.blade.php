@extends('admin.layouts.layout')

@section('content')
<div class="container-xxl">
    <form action="{{ route('product.bulkRestore') }}" method="POST">
        @csrf
        @method('POST')
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Sản Phẩm Đã Xóa</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover table-centered">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th style="width: 20px;">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="checkAll">
                                            <label class="form-check-label" for="checkAll"></label>
                                        </div>
                                    </th>
                                    <th>STT</th>
                                    <th>Tên</th>
                                    <th>Thương Hiệu</th>
                                    <th>Danh Mục</th>
                                    <th>Ảnh</th>
                                    <th>Ngày Xóa</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($trashedProducts && $trashedProducts->count() > 0)
                                    @foreach($trashedProducts as $product)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" name="ids[]" value="{{ $product->id }}" class="form-check-input checkbox-item">
                                                    <label class="form-check-label"> </label>
                                                </div>
                                            </td>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->brand->name ?? 'N/A' }}</td>
                                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($product->image_primary)
                                                    <img src="{{ asset('storage/' . $product->image_primary) }}" alt="{{ $product->name }}" width="50">
                                                @else
                                                    Không Có Ảnh
                                                @endif
                                            </td>
                                            <td>{{ $product->deleted_at }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('product.restore', $product->id) }}" class="btn btn-soft-success btn-sm" onclick="return confirm('Khôi phục?')">
                                                        <iconify-icon icon="solar:refresh-circle-broken" class="align-middle fs-18"></iconify-icon>
                                                    </a>
                                                    <a href="{{ route('product.forceDelete', $product->id) }}" class="btn btn-soft-danger btn-sm" onclick="return confirm('Xóa vĩnh viễn?')">
                                                        <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="8">Không tìm thấy sản phẩm đã xóa</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-top">
                        <button type="submit" class="btn btn-primary me-4" onclick="return confirm('Khôi phục các mục đã chọn?')">Khôi Phục Đã Chọn</button>
                        <a href="{{ route('product.listProduct') }}"
                                                class="btn btn-primary me-4">Cancel</a>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkAll = document.getElementById('checkAll');
            const checkboxes = document.querySelectorAll('.checkbox-item');
            checkAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = checkAll.checked);
            });
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    checkAll.checked = [...checkboxes].every(input => input.checked);
                });
            });
        });
    </script>
</div>
@endsection
