@extends('admin.layouts.layout')
@section('content')
<div class="container-xxl">
    <div class="">
        <form action="{{ route('color.store') }}" method="POST"
            id="colorForm">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Thêm màu sắc</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label for="color-title" class="form-label">Tên màu</label>
                                <input type="text" name="name" id="color-title" class="form-control"
                                    placeholder="Nhập tên" value="{{ old('name', $Color->name ?? '') }}">
                                @if ($errors->has('name'))
                                <span style="color: red;">{{ $errors->first('name') }}</span>
                                @endif
                            </div>

                        </div>


                    </div>
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="color-code" class="form-label">Mã màu</label>
                            <input type="color" name="code" id="color-code" class="form-control"
                                placeholder="Nhập mã màu" value="{{ old('code', $Color->code ?? '#FFFFFF') }}">
                            @if ($errors->has('code'))
                            <span style="color: red;">{{ $errors->first('code') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-light mb-3 rounded">
                <div class="row justify-content-end g-2">
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-outline-secondary w-100">Thêm</button>
                    </div>
                    <div class="col-lg-2">
                        <a href="{{ route('color.list') }}" class="btn btn-primary w-100">Hủy</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection