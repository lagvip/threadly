<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage; 

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
            'name'  => 'required|unique:brands,name',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
           
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        Brand::create($data);

        return redirect()->route('brands.index')->with('success', 'Thêm thương hiệu thành công!');
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, $id) // Đổi thành $id nếu bạn không dùng Route Model Binding
    {
        $brand = Brand::findOrFail($id);

        $data = $request->validate([
            'name'  => 'required|string|max:255|unique:brands,name,' . $id, // Tránh lỗi trùng tên với chính nó khi update
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'id_parent' => 'nullable|exists:categories,id' // Mở lại nếu cần thiết
        ]);

        if ($request->hasFile('image')) {
            // SỬA LỖI TẠI ĐÂY: Dùng Storage facade để xóa ảnh cũ
            if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                Storage::disk('public')->delete($brand->image);
            }
            
            // Lưu ảnh mới
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        $brand->update($data);

        return redirect()->route('brands.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);

        if ($brand->products()->withTrashed()->exists()) {
            return redirect()
                ->route('brands.index')
                ->with('error', 'Không thể xóa thương hiệu vì đang có sản phẩm sử dụng.');
        }

        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Đã chuyển thương hiệu vào thùng rác!');
    }

    public function trash()
    {
        $trashedBrands = Brand::onlyTrashed()->get();
        return view('admin.brands.trash', compact('trashedBrands'));
    }

    public function restore($id)
    {
        $brand = Brand::withTrashed()->findOrFail($id);
        $brand->restore();

        return redirect()->route('brands.trash')->with('success', 'Khôi phục thương hiệu thành công!');
    }

    public function forceDelete($id)
    {
        $brand = Brand::withTrashed()->findOrFail($id);

        if ($brand->products()->withTrashed()->exists()) {
            return redirect()
                ->route('brands.trash')
                ->with('error', 'Không thể xóa vĩnh viễn thương hiệu vì đang có sản phẩm sử dụng.');
        }

        // Xóa ảnh vật lý để tránh rác server
        if ($brand->image && Storage::disk('public')->exists($brand->image)) {
            Storage::disk('public')->delete($brand->image);
        }

        $brand->forceDelete();

        return redirect()->route('brands.trash')->with('success', 'Đã xóa vĩnh viễn thương hiệu!');
    }
}