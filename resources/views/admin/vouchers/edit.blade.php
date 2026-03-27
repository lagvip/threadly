@extends('admin.layouts.layout')

@section('content')
<div class="container">
    <h2>Sửa Voucher</h2>

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

    <form method="POST" action="{{ route('vouchers.update',$voucher) }}">
        @csrf
        @method('PUT')
        @include('admin.vouchers.form')
    </form>
</div>
@endsection