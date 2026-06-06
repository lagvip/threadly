@extends('admin.layouts.layout')

@section('content')
<style>
    .inventory-product-search {
        position: relative;
    }

    .inventory-product-results {
        background: #fff;
        border: 1px solid #d8e0ea;
        border-radius: 6px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
        display: none;
        max-height: 220px;
        overflow-y: auto;
        position: fixed;
        z-index: 3000;
    }

    .inventory-product-result {
        border: 0;
        background: transparent;
        display: block;
        padding: 9px 12px;
        text-align: left;
        width: 100%;
    }

    .inventory-product-result:hover {
        background: #f1f5f9;
    }
</style>

<div class="container-fluid">
    <form method="POST" action="{{ route('admin.inventory.receipts.store') }}" id="receipt-form">
        @csrf

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="card-title mb-0">Tạo phiếu nhập kho</h4>
                <a href="{{ route('admin.inventory.receipts.index') }}" class="btn btn-light btn-sm">Quay lại</a>
            </div>

            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Ghi chú chung</label>
                    <textarea name="note" class="form-control" rows="3">{{ old('note') }}</textarea>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle" id="receipt-items-table">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th style="min-width: 280px;">Sản phẩm</th>
                                <th style="min-width: 300px;">Biến thể</th>
                                <th style="width: 140px;">Số lượng</th>
                                <th style="width: 170px;">Giá nhập</th>
                                <th style="min-width: 220px;">Ghi chú dòng</th>
                                <th style="width: 80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(old('items', [['product_variant_id' => '', 'quantity' => 1, 'unit_cost' => '', 'note' => '']]) as $index => $item)
                                <tr class="receipt-item-row">
                                    <td>
                                        <div class="inventory-product-search">
                                            <input type="text" class="form-control product-search-input" placeholder="Gõ tên sản phẩm..." autocomplete="off">
                                            <div class="inventory-product-results product-results"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="hidden" name="items[{{ $index }}][product_variant_id]" class="variant-id-input" value="{{ $item['product_variant_id'] ?? '' }}">
                                        <select class="form-select variant-select" required disabled>
                                            <option value="">Chọn sản phẩm trước</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" min="1" name="items[{{ $index }}][quantity]" class="form-control" value="{{ $item['quantity'] ?? 1 }}" required>
                                    </td>
                                    <td>
                                        <input type="number" min="0" step="1000" name="items[{{ $index }}][unit_cost]" class="form-control" value="{{ $item['unit_cost'] ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][note]" class="form-control" value="{{ $item['note'] ?? '' }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-soft-danger btn-sm remove-row">Xóa</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-light btn-sm" id="add-row">Thêm dòng</button>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <button type="submit" name="submit_action" value="draft" class="btn btn-secondary">Lưu nháp</button>
                <button type="submit" name="submit_action" value="post" class="btn btn-primary" onclick="return confirm('Xác nhận nhập kho và cộng tồn ngay?')">Xác nhận nhập kho</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableBody = document.querySelector('#receipt-items-table tbody');
        const addButton = document.getElementById('add-row');
        const productSearchUrl = @json(route('admin.inventory.products.search'));
        const productVariantsUrl = @json(route('admin.inventory.products.variants'));

        function debounce(callback, delay = 250) {
            let timer = null;

            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => callback.apply(this, args), delay);
            };
        }

        function reindexRows() {
            tableBody.querySelectorAll('.receipt-item-row').forEach(function (row, index) {
                row.querySelectorAll('[name]').forEach(function (input) {
                    input.name = input.name.replace(/items\[\d+\]/, 'items[' + index + ']');
                });
            });
        }

        async function fetchJson(url) {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Không tải được dữ liệu.');
            }

            return response.json();
        }

        function positionResults(row) {
            const input = row.querySelector('.product-search-input');
            const resultsBox = row.querySelector('.product-results');
            const rect = input.getBoundingClientRect();

            resultsBox.style.left = rect.left + 'px';
            resultsBox.style.top = (rect.bottom + 4) + 'px';
            resultsBox.style.width = rect.width + 'px';
        }

        function resetVariantSelect(row, placeholder = 'Chọn sản phẩm trước') {
            const select = row.querySelector('.variant-select');
            const hidden = row.querySelector('.variant-id-input');

            hidden.value = '';
            select.innerHTML = `<option value="">${placeholder}</option>`;
            select.disabled = true;
        }

        async function loadVariants(row, productId) {
            const select = row.querySelector('.variant-select');

            resetVariantSelect(row, 'Đang tải biến thể...');

            const variants = await fetchJson(productVariantsUrl + '?product_id=' + encodeURIComponent(productId));

            if (variants.length === 0) {
                resetVariantSelect(row, 'Sản phẩm chưa có biến thể');
                return;
            }

            select.innerHTML = '<option value="">Chọn màu / size</option>';

            variants.forEach(function (variant) {
                const option = document.createElement('option');
                option.value = variant.id;
                option.textContent = variant.label;
                select.appendChild(option);
            });

            select.disabled = false;
        }

        async function searchProducts(row, keyword) {
            const resultsBox = row.querySelector('.product-results');

            if (keyword.trim().length < 2) {
                resultsBox.style.display = 'none';
                resultsBox.innerHTML = '';
                return;
            }

            const products = await fetchJson(productSearchUrl + '?keyword=' + encodeURIComponent(keyword.trim()));

            resultsBox.innerHTML = '';

            if (products.length === 0) {
                resultsBox.innerHTML = '<div class="px-3 py-2 text-muted">Không tìm thấy sản phẩm</div>';
                positionResults(row);
                resultsBox.style.display = 'block';
                return;
            }

            products.forEach(function (product) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'inventory-product-result';
                button.textContent = '#' + product.id + ' - ' + product.name;
                button.dataset.productId = product.id;
                button.dataset.productName = product.name;
                resultsBox.appendChild(button);
            });

            positionResults(row);
            resultsBox.style.display = 'block';
        }

        const debouncedSearch = debounce(function (input) {
            searchProducts(input.closest('.receipt-item-row'), input.value).catch(function () {
                input.closest('.receipt-item-row').querySelector('.product-results').style.display = 'none';
            });
        });

        addButton.addEventListener('click', function () {
            const firstRow = tableBody.querySelector('.receipt-item-row');
            const newRow = firstRow.cloneNode(true);

            newRow.querySelectorAll('input').forEach(function (input) {
                input.value = input.type === 'number' && input.name.includes('[quantity]') ? '1' : '';
            });
            newRow.querySelector('.product-results').innerHTML = '';
            newRow.querySelector('.product-results').style.display = 'none';
            resetVariantSelect(newRow);

            tableBody.appendChild(newRow);
            reindexRows();
        });

        tableBody.addEventListener('input', function (event) {
            if (event.target.classList.contains('product-search-input')) {
                resetVariantSelect(event.target.closest('.receipt-item-row'));
                debouncedSearch(event.target);
            }
        });

        tableBody.addEventListener('click', function (event) {
            if (event.target.classList.contains('inventory-product-result')) {
                const row = event.target.closest('.receipt-item-row');
                row.querySelector('.product-search-input').value = event.target.dataset.productName;
                row.querySelector('.product-results').style.display = 'none';
                loadVariants(row, event.target.dataset.productId);
                return;
            }

            if (!event.target.classList.contains('remove-row')) {
                return;
            }

            if (tableBody.querySelectorAll('.receipt-item-row').length === 1) {
                return;
            }

            event.target.closest('tr').remove();
            reindexRows();
        });

        tableBody.addEventListener('change', function (event) {
            if (!event.target.classList.contains('variant-select')) {
                return;
            }

            const row = event.target.closest('.receipt-item-row');
            row.querySelector('.variant-id-input').value = event.target.value;
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('.inventory-product-search')) {
                return;
            }

            document.querySelectorAll('.product-results').forEach(function (box) {
                box.style.display = 'none';
            });
        });

        window.addEventListener('resize', function () {
            document.querySelectorAll('.receipt-item-row').forEach(function (row) {
                const resultsBox = row.querySelector('.product-results');

                if (resultsBox && resultsBox.style.display === 'block') {
                    positionResults(row);
                }
            });
        });

        window.addEventListener('scroll', function () {
            document.querySelectorAll('.product-results').forEach(function (box) {
                box.style.display = 'none';
            });
        }, true);
    });
</script>
@endpush
@endsection
