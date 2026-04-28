@extends('admin.layouts.layout')

@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">Danh Sách Liên Hệ Khách Hàng</h4>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>ID</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>Số điện thoại</th>
                                        <th>Tin nhắn</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày gửi</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($contacts as $contact)
                                        <tr>
                                            <td>{{ $contact->id }}</td>
                                            <td>{{ $contact->name }}</td>
                                            <td>{{ $contact->email }}</td>
                                            <td>{{ $contact->phone ?? 'N/A' }}</td>
                                            <td>{{ substr($contact->message, 0, 50) }}{{ strlen($contact->message) > 50 ? '...' : '' }}</td>
                                            <td>
                                                @if($contact->replied)
                                                    <span class="badge bg-success">Đã liên hệ</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Chưa liên hệ</span>
                                                @endif
                                            </td>
                                            <td>{{ $contact->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('listContact.show', $contact->id) }}" class="btn btn-soft-primary btn-sm" title="Xem chi tiết">
                                                    <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">Chưa có liên hệ nào.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($contacts->hasPages())
                        <div class="card-footer">
                            {{ $contacts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection