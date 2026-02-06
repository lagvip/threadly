@extends('admin.layouts.layout')
@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Chi tiết Banner</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4">
                                @php
                                    $detailImagePath = $banner->image;
                                    if ($detailImagePath && str_starts_with($detailImagePath, 'public/')) {
                                        $detailImagePath = substr($detailImagePath, 7);
                                    }
                                    $detailImageUrl = $detailImagePath ? asset('storage/' . $detailImagePath) : null;
                                @endphp
                                <div class="text-center">
                                    @if($detailImageUrl)
                                        <img src="{{ $detailImageUrl }}" alt="{{ $banner->title }}" 
                                            class="img-fluid rounded" style="max-height: 300px;">
                                    @else
                                        <p class="text-muted">Không có ảnh</p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th style="width: 200px;">Tiêu đề:</th>
                                            <td>{{ $banner->title }}</td>
                                        </tr>
                                        <tr>
                                            <th>Link:</th>
                                            <td>
                                                @if($banner->link)
                                                    <a href="{{ $banner->link }}" target="_blank" class="text-primary">{{ $banner->link }}</a>
                                                @else
                                                    <span class="text-muted">Không có link</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Vị trí:</th>
                                            <td><span class="badge bg-info">{{ $banner->position }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Trạng thái:</th>
                                            <td>
                                                @if($banner->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Ngày tạo:</th>
                                            <td>{{ $banner->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Cập nhật lần cuối:</th>
                                            <td>{{ $banner->updated_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    <a href="{{ route('listBanner.editBanner', $banner) }}" class="btn btn-primary">
                                        <i class="bx bx-edit"></i> Chỉnh sửa
                                    </a>
                                    <a href="{{ route('listBanner.list') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back"></i> Quay lại
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
