@extends('client.layouts.master')

@section('content')
@php
    $authUser = auth()->user();

    $avatarUrl = null;
    if (!empty($authUser?->avatar)) {
        $avatarUrl = \Illuminate\Support\Str::startsWith($authUser->avatar, ['http://', 'https://'])
            ? $authUser->avatar
            : asset('storage/' . $authUser->avatar);
    }
@endphp

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 text-center border-bottom bg-light">
                        <div class="mb-3">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}"
                                     alt="{{ $authUser->name }}"
                                     class="rounded-circle border"
                                     style="width:88px;height:88px;object-fit:cover;">
                            @else
                                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                                     style="width:88px;height:88px;font-size:30px;font-weight:700;">
                                    {{ strtoupper(mb_substr($authUser->name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <h5 class="mb-1">{{ $authUser->name ?: 'Người dùng' }}</h5>
                        <div class="text-muted small">{{ $authUser->email }}</div>
                    </div>

                    <div class="list-group list-group-flush">
                        <a href="{{ route('client.account.index') }}"
                           class="list-group-item list-group-item-action {{ request()->routeIs('client.account.index') ? 'active' : '' }}">
                            Tài khoản của tôi
                        </a>

                        <a href="{{ route('client.account.detail') }}"
                           class="list-group-item list-group-item-action {{ request()->routeIs('client.account.detail') ? 'active' : '' }}">
                            Thông tin chi tiết
                        </a>

                        <a href="{{ route('client.orders.index') }}"
                           class="list-group-item list-group-item-action {{ request()->routeIs('client.orders.*') ? 'active' : '' }}">
                            Đơn hàng của tôi
                        </a>

                        <a href="{{ route('client.addresses.index') }}"
                           class="list-group-item list-group-item-action {{ request()->routeIs('client.addresses.*') ? 'active' : '' }}">
                            Sổ địa chỉ
                        </a>
                        <a href="{{ route('client.wallet.index') }}"
                            class="list-group-item list-group-item-action {{ request()->routeIs('client.wallet.*') ? 'active' : '' }}">
                                Ví hoàn tiền demo
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            @if(session('success'))
                <div class="alert alert-success rounded-3">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger rounded-3">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('account_content')
        </div>
    </div>
</div>
@endsection
