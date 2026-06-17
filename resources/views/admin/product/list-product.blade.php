@extends('admin.layouts.layout')

<style>
    .table td,
    .table th {
        vertical-align: middle;
    }

    .table-status-cell {
        white-space: nowrap;
    }

    .table-status-switch {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
        margin: 0;
    }

    .table-status-switch .form-check {
        margin-bottom: 0;
    }

    .table-status-switch .form-check-input {
        width: 2.75rem;
        height: 1.35rem;
        cursor: pointer;
    }

    .table-status-text {
        font-size: 13px;
        font-weight: 600;
        color: #198754;
        white-space: nowrap;
        min-width: 88px;
        margin: 0;
    }

    .table-status-text.inactive {
        color: #dc3545;
    }

    .product-filter-card {
        padding: 18px 20px;
    }

    .product-filter-form {
        width: 100%;
        max-width: 100%;
    }

    .product-filter-grid {
        display: grid;
        grid-template-columns: minmax(260px, 1.4fr) minmax(210px, 1fr) minmax(210px, 1fr) auto;
        gap: 14px;
        align-items: end;
    }

    .product-filter-item {
        min-width: 0;
    }

    .product-filter-item .form-label {
        font-size: 14px;
        font-weight: 600;
        color: #52637a;
        margin-bottom: 7px;
    }

    .product-filter-item .form-control,
    .product-filter-item .form-select {
        width: 100%;
        height: 42px;
        min-width: 0;
        font-size: 14px;
        border-radius: 9px;
        border-color: #d8e0ea;
        color: #52637a;
        box-shadow: none;
    }

    .product-filter-item .form-control:focus,
    .product-filter-item .form-select:focus {
        border-color: #ff6b35;
        box-shadow: 0 0 0 0.15rem rgba(255, 107, 53, 0.12);
    }

    .product-filter-actions {
        display: flex;
        align-items: end;
        gap: 10px;
        white-space: nowrap;
    }

    .product-filter-actions .btn {
        height: 42px;
        min-width: 105px;
        padding: 0 18px;
        border-radius: 9px;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .product-filter-actions .btn-primary {
        background-color: #ff6b35;
        border-color: #ff6b35;
    }

    .product-filter-actions .btn-primary:hover {
        background-color: #f15c25;
        border-color: #f15c25;
    }

    .product-filter-actions .btn-light {
        background-color: #eef2f7;
        border-color: #eef2f7;
        color: #334155;
    }

    .product-filter-actions .btn-light:hover {
        background-color: #e2e8f0;
        border-color: #e2e8f0;
        color: #1e293b;
    }

    @media (max-width: 1199.98px) {
        .product-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .product-filter-actions {
            grid-column: span 2;
        }

        .product-filter-actions .btn {
            flex: 1;
        }
    }

    @media (max-width: 575.98px) {
        .product-filter-card {
            padding: 14px;
        }

        .product-filter-grid {
            grid-template-columns: 1fr;
        }

        .product-filter-actions {
            grid-column: auto;
            flex-direction: column;
            align-items: stretch;
        }

        .product-filter-actions .btn {
            width: 100%;
        }
    }
</style>

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center gap-1 flex-wrap">
                    <h4 class="card-title flex-grow-1 mb-0">Danh sách sản phẩm</h4>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('product.create') }}" class="btn btn-sm btn-primary">
                            Thêm mới sản phẩm
                        </a>

                        <a href="{{ route('product.trash') }}" class="btn btn-soft-danger btn-sm">Đã Xóa</a>

                        <a href="{{ route('product.variant.trash') }}" class="btn btn-soft-danger btn-sm">
                            Biến thể đã xoá
                        </a>

                        <button type="submit"
                                form="bulk-delete-form"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Bạn có chắc chắn muốn xoá các sản phẩm đã chọn?')">
                            Xoá đã chọn
                        </button>
                    </div>
                </div>

                <div class="card-body border-bottom product-filter-card">
                    <form action="{{ route('product.search') }}" method="GET" class="product-filter-form">
                        <div class="product-filter-grid">
                            <div class="product-filter-item">
                                <label class="form-label">Tên sản phẩm</label>
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="Nhập tên sản phẩm..."
                                       value="{{ request('search', $searchTerm ?? '') }}">
                            </div>

                            <div class="product-filter-item">
                                <label class="form-label">Thương hiệu</label>
                                <select name="brand_id" class="form-select">
                                    <option value="">-- Tất cả thương hiệu --</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}"
                                            {{ (string) request('brand_id', $brandId ?? '') === (string) $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="product-filter-item">
                                <label class="form-label">Danh mục</label>
                                <select name="category_id" class="form-select">
                                    <option value="">-- Tất cả danh mục --</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ (string) request('category_id', $categoryId ?? '') === (string) $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="product-filter-actions">
                                <button type="submit" class="btn btn-primary">
                                    Tìm kiếm
                                </button>

                                <a href="{{ route('product.listProduct') }}" class="btn btn-light">
                                    Bỏ lọc
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover table-centered">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th style="width: 20px;">
                                    <div class="form-check ms-1">
                                        <input type="checkbox" class="form-check-input" id="check-all">
                                        <label class="form-check-label" for="check-all"></label>
                                    </div>
                                </th>
                                <th>STT</th>
                                <th>Tên sản phẩm</th>
                                <th>Thương hiệu</th>
                                <th>Danh mục</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>
                                        <div class="form-check ms-1">
                                            <input type="checkbox"
                                                   class="form-check-input row-checkbox"
                                                   value="{{ $product->id }}"
                                                   name="ids[]"
                                                   id="product-{{ $product->id }}"
                                                   form="bulk-delete-form">
                                            <label class="form-check-label" for="product-{{ $product->id }}"></label>
                                        </div>
                                    </td>

                                    <td>
                                        {{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded bg-light avatar-md d-flex align-items-center justify-content-center">
                                                <img src="{{ asset('storage/' . $product->image_primary) }}"
                                                     alt=""
                                                     class="avatar-md">
                                            </div>
                                            <div>{{ $product->name }}</div>
                                        </div>
                                    </td>

                                    <td>{{ $product->brand->name ?? 'Không có' }}</td>
                                    <td>{{ $product->category->name ?? 'Không có' }}</td>

                                    <td class="table-status-cell">
                                        <form action="{{ route('product.toggleStatus', $product->id) }}"
                                              method="POST"
                                              class="table-status-switch">
                                            @csrf
                                            <input type="hidden" name="status" value="0">

                                            <div class="form-check form-switch">
                                                <input type="checkbox"
                                                       class="form-check-input product-status-switch"
                                                       name="status"
                                                       value="1"
                                                       {{ $product->status === $activeProductStatus ? 'checked' : '' }}
                                                       onchange="this.form.submit()">
                                            </div>

                                            <span class="table-status-text {{ $product->status === $activeProductStatus ? '' : 'inactive' }}">
                                                {{ $productStatusOptions[$product->status] ?? $product->status }}
                                            </span>
                                        </form>
                                    </td>

                                    <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>

                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('product.detail', $product->id) }}"
                                               class="btn btn-light btn-sm">
                                                <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                                            </a>

                                            <a href="{{ route('product.edit', $product->id) }}"
                                               class="btn btn-soft-primary btn-sm">
                                                <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
                                            </a>

                                            <a href="{{ route('product.destroy', $product->id) }}"
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa?')"
                                               class="btn btn-soft-danger btn-sm">
                                                <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Không có sản phẩm nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <form id="bulk-delete-form" action="{{ route('product.bulkDelete') }}" method="POST" class="d-none">
                    @csrf
                </form>

                <div class="p-3">
                    {{ $products->appends([
                        'search' => request('search', $searchTerm ?? ''),
                        'brand_id' => request('brand_id', $brandId ?? ''),
                        'category_id' => request('category_id', $categoryId ?? ''),
                    ])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Xử lý sự kiện cho checkbox "Chọn tất cả" và các checkbox hàng
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('check-all');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkForm = document.getElementById('bulk-delete-form');

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                rowCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = checkAll.checked;
                });
            });
        }

        rowCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const total = rowCheckboxes.length;
                const checked = document.querySelectorAll('.row-checkbox:checked').length;

                if (checkAll) {
                    checkAll.checked = total > 0 && checked === total;
                }
            });
        });

        if (bulkForm) {
            bulkForm.addEventListener('submit', function (e) {
                const checked = document.querySelectorAll('.row-checkbox:checked').length;

                if (checked === 0) {
                    e.preventDefault();
                    alert('Vui lòng chọn ít nhất 1 sản phẩm để xoá');
                }
            });
        }
    });
</script>
@endsection
