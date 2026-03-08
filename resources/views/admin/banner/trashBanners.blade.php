@extends('admin.layouts.layout')
@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">Banners - Thùng rác</h4>
                        <a href="{{ route('listBanner.list') }}" class="btn btn-sm btn-light">
                            Quay lại danh sách
                        </a>
                    </div>
                    <div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Banner</th>
                                        <th>Link</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                        <th>Deleted At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($banners as $key => $value)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @php
                                                        $imagePath = $value->image;
                                                        if ($imagePath && str_starts_with($imagePath, 'public/')) {
                                                            $imagePath = substr($imagePath, 7);
                                                        }
                                                    @endphp
                                                    <div class="rounded bg-light avatar-md d-flex align-items-center justify-content-center">
                                                        <img src="{{ $imagePath ? asset('storage/' . $imagePath) : asset('admin/assets/images/placeholder.png') }}" alt="" class="avatar-md">
                                                    </div>
                                                    <p class="text-dark fw-medium fs-15 mb-0">{{ $value->title }}</p>
                                                </div>
                                            </td>
                                            <td>
                                                @if($value->link)
                                                    <a href="{{ $value->link }}" target="_blank" class="text-primary">{{ Str::limit($value->link, 30) }}</a>
                                                @else
                                                    <span class="text-muted">No Link</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $value->position }}</span>
                                            </td>
                                            <td>
                                                @if($value->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $value->deleted_at }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('listBanner.restore', $value->id) }}" class="btn btn-soft-success btn-sm" onclick="return confirm('Khôi phục banner này?')">
                                                        <iconify-icon icon="solar:undo-left-broken" class="align-middle fs-18"></iconify-icon>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer border-top">
                        {{ $banners->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
