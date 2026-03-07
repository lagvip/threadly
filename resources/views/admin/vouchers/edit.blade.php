@extends('admin.layouts.layout')

@section('content')
<div class="container">
    <h2>Sửa Voucher</h2>

    <form method="POST" action="{{ route('vouchers.update',$voucher) }}">
        @csrf
        @method('PUT')
        @include('admin.vouchers.form')
    </form>
</div>
@endsection