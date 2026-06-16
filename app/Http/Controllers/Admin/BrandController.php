<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Brands\StoreBrandRequest;
use App\Http\Requests\Admin\Brands\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\Admin\Brands\AdminBrandService;
use RuntimeException;

class BrandController extends Controller
{
    public function __construct(protected AdminBrandService $brands) {}

    public function index()
    {
        $this->authorize('viewAny', Brand::class);

        return view('admin.brands.index', $this->brands->indexData());
    }

    public function create()
    {
        $this->authorize('create', Brand::class);

        return view('admin.brands.create');
    }

    public function store(StoreBrandRequest $request)
    {
        $this->authorize('create', Brand::class);

        $this->brands->create($request->validated(), $request->file('image'));

        return redirect()->route('brands.index')->with('success', 'Thêm thương hiệu thành công!');
    }

    public function edit($id)
    {
        $this->authorize('updateAny', Brand::class);

        return view('admin.brands.edit', ['brand' => $this->brands->find((int) $id)]);
    }

    public function update(UpdateBrandRequest $request, $id)
    {
        $this->authorize('updateAny', Brand::class);

        $this->brands->update((int) $id, $request->validated(), $request->file('image'));

        return redirect()->route('brands.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy($id)
    {
        $this->authorize('deleteAny', Brand::class);

        try {
            $this->brands->softDelete((int) $id);

            return redirect()->route('brands.index')->with('success', 'Đã chuyển thương hiệu vào thùng rác!');
        } catch (RuntimeException $e) {
            return redirect()->route('brands.index')->with('error', $e->getMessage());
        }
    }

    public function trash()
    {
        $this->authorize('viewAny', Brand::class);

        return view('admin.brands.trash', $this->brands->trashData());
    }

    public function restore($id)
    {
        $this->authorize('restore', Brand::class);

        $this->brands->restore((int) $id);

        return redirect()->route('brands.trash')->with('success', 'Khôi phục thương hiệu thành công!');
    }

    public function forceDelete($id)
    {
        $this->authorize('forceDelete', Brand::class);

        try {
            $this->brands->forceDelete((int) $id);

            return redirect()->route('brands.trash')->with('success', 'Đã xóa vĩnh viễn thương hiệu!');
        } catch (RuntimeException $e) {
            return redirect()->route('brands.trash')->with('error', $e->getMessage());
        }
    }
}
