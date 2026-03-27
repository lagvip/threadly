@extends('admin.layouts.layout')

@section('content')
<div class="container">
    <h2>Thêm Voucher</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('vouchers.store') }}">
        @csrf
        @include('admin.vouchers.form')
    </form>
</div>
@endsection