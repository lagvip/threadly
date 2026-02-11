@extends('admin.layouts.layout')

@section('content')

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h4 class="fw-bold text-primary mb-0"><i class="fas fa-shipping-fast me-2"></i>Cấu Hình Vận Chuyển</h4>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas {{ isset($shippingEdit) ? 'fa-edit' : 'fa-plus' }} me-1"></i> 
                    {{ isset($shippingEdit) ? 'Sửa thông tin' : 'Thêm đơn vị vận chuyển' }}
                </div>
                <div class="card-body bg-light">
                    
                    <form action="{{ isset($shippingEdit) ? route('admin.shipping.update', $shippingEdit->id) : route('admin.shipping.store') }}" method="POST">
                        @csrf
                        @if(isset($shippingEdit)) @method('PUT') @endif

                        <div class="form-floating mb-3">
                            <input type="text" name="provider_name" class="form-control" id="providerInput" 
                                   value="{{ isset($shippingEdit) ? $shippingEdit->provider_name : old('provider_name') }}"
                                   placeholder="VD: Giao Hàng Nhanh" required>
                            <label for="providerInput">Tên nhà cung cấp</label>
                        </div>
                        
                        {{-- HÀNG CHỨA 2 Ô GIÁ --}}
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="form-floating">
                                    <input type="number" name="price_inner" class="form-control text-success fw-bold" id="priceInner" 
                                           value="{{ isset($shippingEdit) ? $shippingEdit->price_inner : old('price_inner') }}"
                                           placeholder="0" required>
                                    <label for="priceInner">Giá Nội thành</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating">
                                    <input type="number" name="price_outer" class="form-control text-primary fw-bold" id="priceOuter" 
                                           value="{{ isset($shippingEdit) ? $shippingEdit->price_outer : old('price_outer') }}"
                                           placeholder="0" required>
                                    <label for="priceOuter">Giá Ngoại thành</label>
                                </div>
                            </div>
                        </div>

                        {{-- Nếu đang tạo mới thì cho chọn trạng thái luôn --}}
                        

                        <button type="submit" class="btn {{ isset($shippingEdit) ? 'btn-warning text-dark' : 'btn-primary' }} w-100 py-2 fw-bold shadow-sm">
                            <i class="fas {{ isset($shippingEdit) ? 'fa-save' : 'fa-plus-circle' }} me-1"></i> 
                            {{ isset($shippingEdit) ? 'Cập nhật' : 'Thêm mới' }}
                        </button>

                        @if(isset($shippingEdit))
                            <a href="{{ route('admin.shipping.index') }}" class="btn btn-outline-secondary w-100 mt-2">Hủy bỏ</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold text-dark">
                    Danh sách bảng giá
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="ps-3">ID</th>
                                    <th>Nhà cung cấp</th>
                                    <th>Phí Nội thành</th>
                                    <th>Phí Ngoại thành</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shippings as $ship)
                                <tr class="{{ isset($shippingEdit) && $shippingEdit->id == $ship->id ? 'table-active' : '' }}">
                                    <td class="ps-3 fw-bold text-muted">#{{ $ship->id }}</td>
                                    
                                    <td><span class="fw-bold text-dark">{{ $ship->provider_name }}</span></td>
                                    
                                    {{-- Giá Nội thành --}}
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                                            {{ number_format($ship->price_inner) }} ₫
                                        </span>
                                    </td>
                                    
                                    {{-- Giá Ngoại thành --}}
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1">
                                            {{ number_format($ship->price_outer) }} ₫
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.shipping.edit', $ship->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-pen"></i></a>
                                            <a href="{{ route('admin.shipping.delete', $ship->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa?')"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection