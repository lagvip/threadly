@extends('admin.layouts.layout')

@section('content')
<div class="container-xxl">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('reviews.bulkRestore') }}" method="POST">
        @csrf
        @method('POST')

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <h4 class="card-title mb-0">Bình Luận Đã Xóa</h4>
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
                                    <th>Người dùng</th>
                                    <th>Sản phẩm</th>
                                    <th>Biến thể</th>
                                    <th>Đánh giá</th>
                                    <th>Bình luận</th>
                                    <th>Ngày Xóa</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($trashedReviews as $review)
                                    @php
                                        $reviewColor = $review->color_snapshot ?? optional($review->variant)->color?->name;
                                        $reviewSize = $review->size_snapshot ?? optional($review->variant)->size?->name;

                                        $reviewImage = optional($review->variant)->image
                                            ?: optional($review->product)->image_primary;

                                        $reviewImageUrl = $reviewImage
                                            ? asset('storage/' . $reviewImage)
                                            : asset('images/placeholder-80x80.png');

                                        $reviewUserName = $review->user->name
                                            ?? trim(($review->user->first_name ?? '') . ' ' . ($review->user->last_name ?? ''))
                                            ?: 'Không có tên';
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox"
                                                       name="ids[]"
                                                       value="{{ $review->id }}"
                                                       class="form-check-input checkbox-item">
                                                <label class="form-check-label"></label>
                                            </div>
                                        </td>

                                        <td>
                                            {{ $loop->iteration + ($trashedReviews->currentPage() - 1) * $trashedReviews->perPage() }}
                                        </td>

                                        <td>{{ $reviewUserName }}</td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                                     style="width:56px;height:56px;overflow:hidden;">
                                                    <img src="{{ $reviewImageUrl }}"
                                                         alt="{{ $review->product->name ?? 'Sản phẩm' }}"
                                                         style="width:56px;height:56px;object-fit:cover;">
                                                </div>
                                                <div style="min-width: 0;">
                                                    {{ $review->product->name ?? 'Sản phẩm đã bị xóa' }}
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            @if($reviewColor || $reviewSize)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @if($reviewColor)
                                                        <span class="badge bg-light text-dark border">Màu: {{ $reviewColor }}</span>
                                                    @endif
                                                    @if($reviewSize)
                                                        <span class="badge bg-light text-dark border">Size: {{ $reviewSize }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">Không có</span>
                                            @endif
                                        </td>

                                        <td>
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fa{{ $i <= $review->rating ? 's' : 'r' }} fa-star text-warning"></i>
                                            @endfor
                                        </td>

                                        <td style="max-width: 280px; white-space: normal;">
                                            {{ $review->comment ?? 'Không có bình luận' }}
                                        </td>

                                        <td>{{ optional($review->deleted_at)->format('d/m/Y H:i') }}</td>

                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('reviews.restore', $review->id) }}"
                                                   class="btn btn-soft-success btn-sm"
                                                   onclick="return confirm('Khôi phục bình luận này?')">
                                                    <iconify-icon icon="solar:refresh-circle-broken" class="align-middle fs-18"></iconify-icon>
                                                </a>

                                                <a href="{{ route('reviews.forceDelete', $review->id) }}"
                                                   class="btn btn-soft-danger btn-sm"
                                                   onclick="return confirm('Xóa vĩnh viễn bình luận này?')">
                                                    <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Không tìm thấy bình luận đã xóa</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <button type="submit"
                                    class="btn btn-primary me-2"
                                    onclick="return confirm('Khôi phục các bình luận đã chọn?')">
                                Khôi Phục Đã Chọn
                            </button>

                            <a href="{{ route('reviews.index') }}" class="btn btn-light">
                                Quay lại
                            </a>
                        </div>

                        <div>
                            {{ $trashedReviews->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkAll = document.getElementById('checkAll');
            const checkboxes = document.querySelectorAll('.checkbox-item');

            if (checkAll) {
                checkAll.addEventListener('change', function () {
                    checkboxes.forEach(cb => cb.checked = checkAll.checked);
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    checkAll.checked = [...checkboxes].length > 0 && [...checkboxes].every(input => input.checked);
                });
            });
        });
    </script>
</div>
@endsection
