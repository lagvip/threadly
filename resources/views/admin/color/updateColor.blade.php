@extends('admin.layouts.layout')
@section('content')
<div class="container-xxl">

    <div class="row">
        <div class="col-xl-3 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-lg-4 col-4">
                                <p class="mb-1 mt-2">Name:</p>
                                <h5 class="mb-0">{{ $color['name'] }}</h5>
                            </div>
                            <div class="col-lg-4 col-4">
                                <p class="mb-1 mt-2">Code:</p>
                                <h5 class="mb-0">{{ $color['code'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-9 col-lg-8 ">
            <form action="{{ route('listColor.updateColor', $color) }}" method="POST"
                enctype="multipart/form-data" id="updateColorForm"> {{-- Đổi ID form --}}
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Cập nhật màu sắc</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="color-title" class="form-label">Tên</label>
                                    <input type="text" name="name" value="{{ $color['name'] }}"
                                        id="color-title" class="form-control" placeholder="Nhập tên">
                                    @if ($errors->has('name'))
                                    <span style="color: red;">{{ $errors->first('name') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="color-code" class="form-label">Mã màu</label>
                                    <input type="text" name="code" value="{{ $color['code'] }}"
                                        id="color-code" class="form-control" placeholder="Nhập mã màu">
                                    @if ($errors->has('code'))
                                    <span style="color: red;">{{ $errors->first('code') }}</span>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light mb-3 rounded">
                    <div class="row justify-content-end g-2">
                        <div class="col-lg-2">
                            <button type="submit" class="btn btn-outline-secondary w-100">Cập nhật</button>
                        </div>

                        <div class="col-lg-2">
                            <a href="{{ route('listCategory.list') }}" class="btn btn-primary w-100">Hủy</a>
                            {{-- Đổi link hủy --}}
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection