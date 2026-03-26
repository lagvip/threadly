<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::all();
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

   public function store(Request $request)
{
    $request->validate([
        'name' => 'required|unique:brands,name',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate ảnh
    ]);

    $data = $request->all();

    if ($request->hasFile('image')) {
        // Lưu ảnh vào thư mục storage/app/public/brands
        $path = $request->file('image')->store('brands', 'public');
        $data['image'] = $path;
    }

    Brand::create($data);

    return redirect()->route('brands.index')->with('success', 'Thêm thành công!');
}
    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }
    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $id,
        ]);
        $brand->update([
            'name' => $request->name,
        ]);

        return redirect()->route('brands.index')
            ->with('success', 'Cập nhật thương hiệu thành công.');
    }


        public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete(); // Lúc này chỉ là Soft Delete (deleted_at sẽ có giá trị)

        return redirect()->route('brands.index')->with('success', 'Đã chuyển thương hiệu vào thùng rác!');
    }
        public function trash()
    {
        // Chỉ lấy các bản ghi đã bị xóa mềm (deleted_at IS NOT NULL)
        $trashedBrands = Brand::onlyTrashed()->get();

        return view('admin.brands.trash', compact('trashedBrands'));
    }
    // 1. Khôi phục bản ghi
public function restore($id)
{
    $brand = Brand::withTrashed()->findOrFail($id);
    $brand->restore();

    return redirect()->route('brands.trash')->with('success', 'Khôi phục thương hiệu thành công!');
}

// 2. Xóa hẳn khỏi Database
public function forceDelete($id)
{
    $brand = Brand::withTrashed()->findOrFail($id);
    
    // Xóa ảnh vật lý trong thư mục storage trước khi xóa DB
    if ($brand->image) {
        \Storage::disk('public')->delete($brand->image);
    }

    $brand->forceDelete();

    return redirect()->route('brands.trash')->with('success', 'Đã xóa vĩnh viễn thương hiệu!');
}
}
