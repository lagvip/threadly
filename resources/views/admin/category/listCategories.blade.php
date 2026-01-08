@extends('admin.layouts.layout')
@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">All Categories List</h4>
                        <a href="{{ route('listCategory.addCategory') }}" class="btn btn-sm btn-primary">
                            Add Category
                        </a>
                        <form action="{{ route('listCategory.searchCategory') }}" method="GET">
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
                                        <th>Categories</th>
                                        <th>Parent Category</th> {{-- Thêm cột này --}}
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($category as $key => $value)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="customCheck2">
                                                    <label class="form-check-label" for="customCheck2"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div
                                                        class="rounded bg-light avatar-md d-flex align-items-center justify-content-center">
                                                        <img src="{{ Storage::url($value->image) }}" alt=""
                                                            class="avatar-md">
                                                    </div>
                                                    <p class="text-dark fw-medium fs-15 mb-0">{{ $value->name }}</p>
                                                </div>

                                            </td>
                                            <td> {{-- Thêm cột hiển thị tên category cha --}}
                                                @if ($value->parent) {{-- Kiểm tra xem có category cha không --}}
                                                    {{ $value->parent->name }}
                                                @else
                                                    No Parent
                                                @endif
                                            </td>

                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('listCategory.detailCategory', $value) }}"
                                                        class="btn btn-light btn-sm"><iconify-icon icon="solar:eye-broken"
                                                            class="align-middle fs-18"></iconify-icon></a>
                                                    <a href="{{ route('listCategory.editCategory', $value) }}"
                                                        class="btn btn-soft-primary btn-sm"><iconify-icon
                                                            icon="solar:pen-2-broken"
                                                            class="align-middle fs-18"></iconify-icon></a>

                                                    <form action="{{ route('listCategory.deleteCategory', $value) }}"
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
                        {{ $category->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>


    @endsection