@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Quản lý đánh giá sản phẩm</h1>

    <!-- Tìm kiếm và lọc -->
    <form method="GET" action="" class="row mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tìm theo đánh giá hoặc bình luận...">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-control">
                <option value="">-- Lọc theo trạng thái phản hồi --</option>
                <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Đã phản hồi</option>
                <option value="unreplied" {{ request('status') == 'unreplied' ? 'selected' : '' }}>Chưa phản hồi</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="rating" class="form-control">
                <option value="">-- Lọc theo số sao --</option>
                @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                        {{ $i }} sao
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary">Lọc</button>
        </div>
    </form>

    <!-- Danh sách đánh giá -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Người dùng</th>
                <th>Sản phẩm</th>
                <th>Đánh giá</th>
                <th>Bình luận</th>
                <th>Phản hồi</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reviews as $review)
                <tr>
                    <td>{{ $review->id }}</td>
                    <td>{{ $review->user->name ?? 'Không có tên' }}</td>
                    <td>{{ $review->product->name }}</td>
                    <td>
                        <div class="d-inline-flex gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fa{{ $i <= $review->rating ? 's' : 'r' }} fa-star text-warning"></i>
                            @endfor
                        </div>
                    </td>

                    <td>{{ $review->comment ?? 'Không có bình luận' }}</td>
                    <td>
                        @if ($review->admin_reply)
                             <strong>{{ $review->admin->name ?? 'Admin' }}:</strong> {{ $review->admin_reply }}<br>
                                <small>{{ $review->updated_at->format('d/m/Y H:i') }}</small>

                        @else
                            <span class="text-danger">Chưa phản hồi</span>
                        @endif
                    </td>
                   <td>
                        <div class="d-flex align-items-center gap-2">
                            @if ($review->admin_reply)
                                {{-- Nếu đã phản hồi: Sửa + Xóa --}}
                                <a href="{{ route('reviews.edit', $review->id) }}" class="btn btn-sm btn-warning">Sửa</a>

                                <form action="{{ route('reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            @else
                                {{-- Nếu chưa phản hồi: Phản hồi + Xóa --}}
                                <a href="{{ route('reviews.edit', $review->id) }}"
                                class="btn btn-sm btn-success d-inline-block"
                                style="padding: 4px 10px; font-size: 0.875rem; line-height: 1; border-radius: 0.5rem;">
                                Phản hồi
                                </a>

                                <form action="{{ route('reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            @endif
                        </div>
                    </td>


                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $reviews->withQueryString()->links() }}
</div>
@endsection
