@extends('admin.layouts.layout')
@section('content')
    <!-- Start Container Fluid -->

    <div class="container-xxl">

        <div class="row">
            <div class="">
                <div class="card">
                    <div class="card-body">
                        <div class="mt-3">

                            <div class="row">
                                <div class="col-lg-4 col-4">
                                    <p class="mb-1 mt-2">Name:</p>
                                    <h3 class="mb-0">{{ $color['name'] }}</h3>
                                </div>
                                <div class="col-lg-4 col-4">
                                    <p class="mb-1 mt-2">Color code</p>
                                    <h3 class="mb-0">{{ $color['code'] }}</h3>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- End Container Fluid -->
@endsection
