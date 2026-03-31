@extends('admin.layouts.layout')
@section('content')
    <div class="container-xxl">
        <div class="">
            @include('admin.banner.partials.banner-form', [
                'action' => route('listBanner.storeBanner'),
                'formId' => 'bannerForm',
                'submitLabel' => 'Thêm'
            ])
        </div>
    </div>
@endsection
