@extends('admin.layouts.layout')

@section('content')
<div class="container">
    <h2>Thêm Voucher</h2>

    <form method="POST" action="{{ route('vouchers.store') }}">
        @csrf
        @include('admin.vouchers.form')
    </form>
</div>
@endsection
