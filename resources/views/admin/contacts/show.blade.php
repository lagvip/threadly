@extends('admin.layouts.layout')

@section('content')
    <div class="container-xxl">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">Chi Tiết Liên Hệ</h4>
                        <a href="{{ route('listContact.list') }}" class="btn btn-sm btn-outline-secondary">
                            Quay lại
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><strong>Họ tên:</strong></label>
                            <p class="fs-15">{{ $contact->name }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Email:</strong></label>
                            <p class="fs-15">
                                <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Số điện thoại:</strong></label>
                            <p class="fs-15">{{ $contact->phone ?? 'Không có' }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Tin nhắn:</strong></label>
                            <p class="fs-15">{{ $contact->message }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Trạng thái:</strong></label>
                            <p class="fs-15">
                                @if($contact->replied)
                                    <span class="badge bg-success">Đã liên hệ</span>
                                    @if($contact->replied_at)
                                        <small class="text-muted ms-2">({{ $contact->replied_at->format('d/m/Y H:i') }})</small>
                                    @endif
                                @else
                                    <span class="badge bg-warning text-dark">Chưa liên hệ</span>
                                @endif
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Ngày gửi:</strong></label>
                            <p class="fs-15">{{ $contact->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="card-footer d-flex gap-2">
                        <form action="{{ route('listContact.toggleReplied', $contact->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn {{ $contact->replied ? 'btn-warning' : 'btn-success' }}">
                                @if($contact->replied)
                                    <iconify-icon icon="solar:undo-left-broken" class="align-middle me-2 fs-18"></iconify-icon>
                                    Đánh dấu chưa liên hệ
                                @else
                                    <iconify-icon icon="solar:check-circle-broken" class="align-middle me-2 fs-18"></iconify-icon>
                                    Đánh dấu đã liên hệ
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
