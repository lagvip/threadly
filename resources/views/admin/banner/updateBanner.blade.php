@extends('admin.layouts.layout')
@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-xl-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="bg-light text-center rounded bg-light">
                            @php
                                $bannerImagePath = $banner['image'] ?? null;
                                if ($bannerImagePath && str_starts_with($bannerImagePath, 'public/')) {
                                    $bannerImagePath = substr($bannerImagePath, 7);
                                }
                                $bannerImageUrl = $bannerImagePath ? asset('storage/' . $bannerImagePath) : null;
                            @endphp
                            @if ($bannerImageUrl)
                                <img src="{{ $bannerImageUrl }}" alt="Current Image" class="avatar-xxl"
                                    id="leftImagePreview">
                            @else
                                <p>Không có ảnh hiện tại.</p>
                                <img src="" alt="Image Placeholder" class="avatar-xxl" id="leftImagePreview"
                                    style="display: none;">
                            @endif
                        </div>
                        <div class="mt-3">
                            <h4>{{ $banner['title'] }}</h4>
                            <div class="row">
                                <div class="col-lg-12">
                                    <p class="mb-1 mt-2">Tiêu đề:</p>
                                    <h5 class="mb-0">{{ $banner['title'] }}</h5>
                                </div>
                                <div class="col-lg-12 mt-2">
                                    <p class="mb-1">Vị trí:</p>
                                    <h5 class="mb-0">{{ $banner['position'] }}</h5>
                                </div>
                                <div class="col-lg-12 mt-2">
                                    <p class="mb-1">Trạng thái:</p>
                                    @if($banner['is_active'])
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-9 col-lg-8 ">
                @include('admin.banner.partials.banner-form', [
                    'action' => route('listBanner.updateBanner', $banner),
                    'method' => 'PUT',
                    'formId' => 'updateBannerForm',
                    'submitLabel' => 'Cập nhật',
                    'banner' => $banner
                ])
            </div>
        </div>
    </div>
@endsection
