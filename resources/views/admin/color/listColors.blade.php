@extends('admin.layouts.layout')
@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">All Colors List</h4>
                        <a href="{{ route('listColor.addColor') }}" class="btn btn-sm btn-primary">
                            Add Color
                        </a>
                        <form action="{{ route('listColor.searchColor') }}" method="GET">
                            <div class="search-bar">    
                            <span><i class="bx bx-search-alt"></i></span>
                            <input name="search" type="search" class="form-control" id="search" placeholder="Search task...">
                        </div>
                        </form>


                    </div>
                    <div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th style="width: 20px;">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="customCheck1">
                                                <label class="form-check-label" for="customCheck1"></label>
                                            </div>
                                        </th>
                                        <th>Color</th>
                                        <th>Color code</th> {{-- Thêm cột này --}}
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($colors as $key => $value)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="customCheck2">
                                                    <label class="form-check-label" for="customCheck2"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <p class="text-dark fw-medium fs-15 mb-0">{{ $value->name }}</p>
                                                </div>

                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <p class="text-dark fw-medium fs-15 mb-0">{{ $value->code }}</p>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('listColor.detailColor', $value) }}"
                                                        class="btn btn-light btn-sm"><iconify-icon icon="solar:eye-broken"
                                                            class="align-middle fs-18"></iconify-icon></a>
                                                    <a href="{{ route('listColor.editColor', $value) }}"
                                                        class="btn btn-soft-primary btn-sm"><iconify-icon
                                                            icon="solar:pen-2-broken"
                                                            class="align-middle fs-18"></iconify-icon></a>

                                                    <form action="{{ route('listColor.deleteColor', $value) }}"
                                                        method="post">
                                                        @csrf
                                                        @method ('DELETE')
                                                        <button onclick="return confirm('bạn có chắc muốn xoá ko')"
                                                            type="submit" class="btn btn-soft-danger btn-sm"><iconify-icon
                                                                icon="solar:trash-bin-minimalistic-2-broken"
                                                                class="align-middle fs-18">
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        </div>
                    <div class="card-footer border-top">
                        {{ $colors->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>


    @endsection