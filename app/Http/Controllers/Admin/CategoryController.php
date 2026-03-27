<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $category = Category::with('parent')->latest('id')->paginate(10);
        return view('admin.category.listCategories', compact('category'));
    }

    public function create()
    {
        // Chỉ cho phép chọn category gốc làm cha
        $categories = Category::whereNull('id_parent')->get();

        return view('admin.category.addCategory', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => ['required', Rule::unique('categories', 'name')],
                'image' => ['required', 'image', 'max:2048'],
                'id_parent' => ['nullable', 'exists:categories,id'],
            ],
            [
                'name.required' => 'Bạn chưa nhập tên.',
                'name.unique' => 'Tên này đã tồn tại, vui lòng chọn tên khác.',
                'image.required' => 'Bạn chưa chọn ảnh.',
                'image.image' => 'File phải là ảnh hợp lệ.',
                'image.max' => 'Ảnh không được vượt quá 2MB.',
                'id_parent.exists' => 'Danh mục cha không tồn tại.',
            ]
        );

        if ($request->filled('id_parent')) {
            $parent = Category::findOrFail($request->id_parent);

            // Chỉ cho phép 1 tầng: cha phải là category gốc
            if (!is_null($parent->id_parent)) {
                return back()
                    ->withErrors(['id_parent' => 'Chỉ được chọn danh mục gốc làm danh mục cha.'])
                    ->withInput();
            }
        }

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = Storage::disk('public')->putFile('category', $request->file('image'));
        }

        $data['id_parent'] = $request->filled('id_parent') ? $request->id_parent : null;

        Category::create($data);

        return redirect()->route('listCategory.list')->with('success', 'Thêm thành công');
    }

    public function show(string $id)
    {
        $category = Category::with(['parent', 'children'])->findOrFail($id);

        $childIds = $category->children->pluck('id')->toArray();
        $categoryIds = array_merge([$category->id], $childIds);

        $products = Product::with(['brand', 'category'])
            ->whereIn('id_category', $categoryIds)
            ->latest('id')
            ->paginate(10);

        return view('admin.category.detailCategory', compact('category', 'products'));
    }

    public function edit(string $id)
    {
        $category = Category::findOrFail($id);

        $allCategories = Category::whereNull('id_parent')
            ->where('id', '!=', $category->id)
            ->get();

        return view('admin.category.updateCategory', compact('category', 'allCategories'));
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'id_parent' => ['nullable', 'exists:categories,id'],
        ], [
            'name.required' => 'Tên danh mục không được để trống.',
            'name.unique' => 'Tên danh mục đã tồn tại.',
            'name.max' => 'Tên danh mục không được vượt quá 255 ký tự.',
            'image.image' => 'File tải lên phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif, svg.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'id_parent.exists' => 'Danh mục cha không tồn tại.',
        ]);

        if ($request->filled('id_parent')) {
            $parentId = (int) $request->id_parent;

            if ($parentId === (int) $category->id) {
                return back()
                    ->withErrors(['id_parent' => 'Danh mục không thể là cha của chính nó.'])
                    ->withInput();
            }

            // Nếu category hiện tại đang là cha thì không cho biến thành con
            if ($category->children()->exists()) {
                return back()
                    ->withErrors(['id_parent' => 'Danh mục đang là danh mục cha, không thể chuyển thành danh mục con.'])
                    ->withInput();
            }

            $parent = Category::findOrFail($parentId);

            // Chỉ cho phép 1 tầng: không được chọn một category con làm cha
            if (!is_null($parent->id_parent)) {
                return back()
                    ->withErrors(['id_parent' => 'Chỉ được chọn danh mục gốc làm danh mục cha.'])
                    ->withInput();
            }
        }

        $data = $request->except('image');

        $currentImage = $category->image;
        $newImagePath = null;

        if ($request->hasFile('image')) {
            $newImagePath = Storage::disk('public')->putFile('category', $request->file('image'));
            $data['image'] = $newImagePath;
        }

        $data['id_parent'] = $request->filled('id_parent') ? $request->id_parent : null;

        $isUpdate = $category->update($data);

        if ($isUpdate && $newImagePath && $currentImage && Storage::disk('public')->exists($currentImage)) {
            Storage::disk('public')->delete($currentImage);
        }

        if ($isUpdate) {
            return redirect()->route('listCategory.list')->with('success', 'Sửa thành công danh mục!');
        }

        return redirect()->route('listCategory.list')->with('error', 'Sửa không thành công!');
    }

    public function destroy(string $id)
{
    $category = Category::findOrFail($id);

    if ($category->children()->exists()) {
        return redirect()
            ->route('listCategory.list')
            ->with('error', 'Không thể xoá danh mục đang có danh mục con.');
    }

    if ($category->products()->exists()) {
        return redirect()
            ->route('listCategory.list')
            ->with('error', 'Không thể xoá danh mục đang có sản phẩm.');
    }

    $category->delete();

    return redirect()->route('listCategory.list')->with('success', 'Đã chuyển danh mục vào thùng rác');
}

    public function search(Request $request)
    {
        $search = $request->input('search');

        $category = Category::with('parent')
            ->where('name', 'like', '%' . $search . '%')
            ->latest('id')
            ->paginate(10);

        return view('admin.category.listCategories', compact('category'));
    }
    // Hiển thị danh sách thùng rác
public function trash()
{
    $category = Category::onlyTrashed()->latest()->paginate(10);
    
    return view('admin.category.trash', compact('category'));
}

// Khôi phục danh mục
public function restore($id)
{
    $category = Category::withTrashed()->findOrFail($id);
    $category->restore();

    return redirect()->route('listCategory.trash')->with('success', 'Khôi phục danh mục thành công!');
}

// Xóa vĩnh viễn
public function forceDelete($id)
{
    $category = Category::withTrashed()->findOrFail($id);
    
    // Nếu có ảnh, xóa ảnh vật lý để tiết kiệm bộ nhớ
    if ($category->image) {
        \Storage::disk('public')->delete($category->image);
    }

    $category->forceDelete();

    return redirect()->route('listCategory.trash')->with('success', 'Đã xóa vĩnh viễn danh mục!');
}
}
