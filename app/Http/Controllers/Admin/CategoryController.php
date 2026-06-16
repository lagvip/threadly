<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Categories\IndexCategoriesRequest;
use App\Http\Requests\Admin\Categories\StoreCategoryRequest;
use App\Http\Requests\Admin\Categories\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\Admin\Categories\AdminCategoryQueryService;
use App\Services\Admin\Categories\AdminCategoryService;
use RuntimeException;

class CategoryController extends Controller
{
    public function __construct(
        protected AdminCategoryQueryService $queries,
        protected AdminCategoryService $categories
    ) {}

    public function index(IndexCategoriesRequest $request)
    {
        $this->authorize('viewAny', Category::class);

        return view('admin.category.listCategories', $this->queries->indexData($request->searchTerm()));
    }

    public function create()
    {
        $this->authorize('create', Category::class);

        return view('admin.category.addCategory', $this->queries->createData());
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->authorize('create', Category::class);

        try {
            $this->categories->create($request->validated(), $request->file('image'));

            return redirect()->route('listCategory.list')->with('success', 'Thêm thành công');
        } catch (RuntimeException $e) {
            return back()->withErrors(['id_parent' => $e->getMessage()])->withInput();
        }
    }

    public function show(string $id)
    {
        $this->authorize('viewAny', Category::class);

        return view('admin.category.detailCategory', $this->queries->detailData((int) $id));
    }

    public function edit(string $id)
    {
        $this->authorize('updateAny', Category::class);

        return view('admin.category.updateCategory', $this->queries->editData((int) $id));
    }

    public function update(UpdateCategoryRequest $request, string $id)
    {
        $this->authorize('updateAny', Category::class);

        try {
            $this->categories->update((int) $id, $request->validated(), $request->file('image'));

            return redirect()->route('listCategory.list')->with('success', 'Sửa thành công danh mục!');
        } catch (RuntimeException $e) {
            return back()->withErrors(['id_parent' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(string $id)
    {
        $this->authorize('deleteAny', Category::class);

        try {
            $this->categories->softDelete((int) $id);

            return redirect()->route('listCategory.list')->with('success', 'Đã chuyển danh mục vào thùng rác');
        } catch (RuntimeException $e) {
            return redirect()->route('listCategory.list')->with('error', $e->getMessage());
        }
    }

    public function search(IndexCategoriesRequest $request)
    {
        $this->authorize('viewAny', Category::class);

        return view('admin.category.listCategories', $this->queries->indexData($request->searchTerm()));
    }

    public function trash()
    {
        $this->authorize('viewAny', Category::class);

        return view('admin.category.trash', $this->queries->trashData());
    }

    public function restore($id)
    {
        $this->authorize('restore', Category::class);

        $this->categories->restore((int) $id);

        return redirect()->route('listCategory.trash')->with('success', 'Khôi phục danh mục thành công!');
    }

    public function forceDelete($id)
    {
        $this->authorize('forceDelete', Category::class);

        $this->categories->forceDelete((int) $id);

        return redirect()->route('listCategory.trash')->with('success', 'Đã xóa vĩnh viễn danh mục!');
    }
}
