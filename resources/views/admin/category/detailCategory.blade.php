@extends('admin.layouts.layout')
@section('content')
    <!-- Start Container Fluid -->

    <div class="container-xxl">

        <div class="row">
            <div class="">
                <div class="card">
                    <div class="card-body">
                        <div class=" text-center rounded ">
                            <img src="{{ Storage::url($category['image']) }}" alt="" width="250">
                        </div>
                        <div class="mt-3">

                            <div class="row">
                                <div class="col-lg-4 col-4">
                                    <p class="mb-1 mt-2">Name:</p>
                                    <h3 class="mb-0">{{ $category['name'] }}</h3>
                                </div>
                                <div class="col-lg-4 col-4">
                                    <p class="mb-1 mt-2">Parent Category:</p>
                                    <h3 class="mb-0">
                                        @if ($category->parent)
                                            {{-- Kiểm tra xem có category cha không --}}
                                            {{ $category->parent->name }}
                                        @else
                                            No Parent
                                        @endif
                                    </h3>
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
