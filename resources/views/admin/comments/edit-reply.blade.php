@extends('admin.layouts.layout')

@section('content')
<div class="container">
    <h3 class="mb-4">{{ $review->admin_reply ? 'Sửa phản hồi' : 'Phản hồi đánh giá' }}</h3>

    <div class="card">
        <div class="card-body">
            <p><strong>Người dùng:</strong> {{ $review->user->first_name }} {{ $review->user->last_name }}</p>
            <p><strong>Sản phẩm:</strong> {{ $review->product->name }}</p>
            <p><strong>Đánh giá:</strong>
                @for ($i = 1; $i <= 5; $i++)
                    <i class="fa{{ $i <= $review->rating ? 's' : 'r' }} fa-star text-warning"></i>
                @endfor
            </p>
            <p><strong>Bình luận:</strong> {{ $review->comment ?? 'Không có bình luận' }}</p>

            <form action="{{ route('reviews.update', $review->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="admin_reply" class="form-label">Phản hồi của admin:</label>
                    <textarea name="admin_reply" id="admin_reply" rows="4" class="form-control">{{ old('admin_reply', $review->admin_reply) }}</textarea>
                    @error('admin_reply')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary">Lưu</button>
                <a href="{{ route('reviews.index') }}" class="btn btn-secondary">Quay lại</a>
            </form>
        </div>
    </div>
</div>
@endsection
