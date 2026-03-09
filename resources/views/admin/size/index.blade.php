@extends('admin.layouts.layout')

@section('content')


 <div class="container-xxl">
    
      <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">Danh sách tất cả các kích cỡ</h4>
                        <a href="{{ route    ('listSize.addSize') }}" class="btn btn-sm btn-primary">
                            Thêm kích cỡ
                        </a>
                        {{-- <form action="{{ route('listSize.list') }}" method="GET">
                            <div class="search-bar">    
                            <span><i class="bx bx-search-alt"></i></span>
                            <input name="search" type="search" class="form-control" id="search" placeholder="Search task...">
                        </div>
                        </form> --}}


                    </div>
                    <div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        {{-- <th style="width: 20px;">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="customCheck1">
                                                <label class="form-check-label" for="customCheck1"></label>
                                            </div>
                                        </th> --}}
                                        <th>ID</th>
                                        <th>Tên kích thước</th>
                                        <th>Ngày tạo</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                   @foreach($sizes as $size)
    <tr>
        <td>{{ $size->id }}</td>
        <td>{{ $size->name }}</td>
        <td>{{ $size->created_at }}</td>
        <td>
            <a href="{{ route('listSize.editSize', $size->id) }}" 
               class="btn btn-warning btn-sm">Sửa</a>

            {{-- <form action="{{ route('listSize.deleteSize', $size->id) }}" 
                  method="POST" 
                  style="display:inline-block;">
                @csrf
                @method('DELETE') --}}
                <button class="btn  btn-danger btn-sm"
                        onclick="return confirm('Xóa?')">
                    Xóa
                </button>
            {{-- </form> --}}
        </td>
    </tr>
    @endforeach
                                </tbody>
                            </table>
                        </div>
                        </div>
                    <div class="card-footer border-top">
                        
                    </div>
                </div>
            </div>
        </div>







{{ $sizes->links() }}

@endsection
