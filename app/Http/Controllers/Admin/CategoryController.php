<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $category = Category::query()->latest('id')->paginate(10);
        return view('admin.category.listCategories', compact('category'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.category.addCategory', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->except('image');

        $request->validate(
            [
                'name' => [
                    'required',
                    Rule::unique('categories')
                ],
                'image' => [
                    'required',
                    'image',
                    'max:2048'
                ],

            ],
            [
                'name.required' => 'Bạn chưa nhập tên.',
                'name.unique' => 'Tên này đã tồn tại, vui lòng chọn tên khác.',
                'image.required' => 'Bạn chưa chọn ảnh.',
                'image.image' => 'File phải là ảnh hợp lệ.',
                'image.max' => 'Ảnh không được vượt quá 2MB.',
            ]
        );
        if ($request->hasFile('image')) {
            // Tự động lưu vào storage/app/category và trả về path
            // dd($request->file('image'));
            // $data['image'] = Storage::put('public/category', $request->file('image'));
            $data['image'] = str_replace('public/', '', Storage::put('public/category', $request->file('image')));

        }
        $data['id_parent'] = $request->input('id_parent');



        Category::create($data);

        return redirect()->route('listCategory.list')->with('success', 'Thêm thành công');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::findOrFail($id);
        return view('admin.category.detailCategory', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        $allCategories = Category::all();
        return view('admin.category.updateCategory', compact('category', 'allCategories'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) // Giả định bạn đang dùng Request và id
    {

        $category = Category::findOrFail($id); // Tìm category theo ID

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ], [
            'name.required' => 'Tên danh mục không được để trống.',
            'name.unique' => 'Tên danh mục đã tồn tại.',
            'name.max' => 'Tên danh mục không được vượt quá 255 ký tự.',
            'image.image' => 'File tải lên phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif, svg.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',

        ]);

        $data = $request->except('image'); // Lấy tất cả dữ liệu trừ 'image'

        $currentImage = $category->image; // Lưu đường dẫn ảnh CŨ trước khi xử lý ảnh mới


        $newImagePath = null; // Biến để lưu đường dẫn ảnh mới nếu có

        if ($request->hasFile('image')) { // Luôn dùng hasFile để kiểm tra file được upload
            // $newImagePath = Storage::put('public/category', $request->file('image')); // Lưu ảnh mới
            $newImagePath = $request->file('image')->store('category', 'public');

            $data["image"] = $newImagePath; // Cập nhật đường dẫn ảnh MỚI vào mảng $data
        }
        $data['id_parent'] = $request->input('id_parent');


        $is_update = $category->update($data); // Cập nhật category vào database

        // --- Logic xóa ảnh cũ chỉ khi update thành công và có ảnh mới ---
        if ($is_update && $newImagePath) { // Nếu update thành công VÀ có ảnh mới được tải lên
            if ($currentImage && Storage::exists($currentImage)) { // Nếu có ảnh cũ VÀ ảnh cũ tồn tại trên storage
                Storage::delete($currentImage); // Xóa ảnh cũ
            }
        }
        // ---------------------------------------------------------------


        if ($is_update) {
            return redirect()->route("listCategory.list")->with("success", "Sửa thành công sản phẩm!");
        } else {
            return redirect()->route("listCategory.list")->with("error", "Sửa không thành công!");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        $imagePath = $category->image;

        if ($imagePath && Storage::exists($imagePath)) {
            Storage::delete($imagePath);
        }

        $category->delete();
        return redirect()->route('listCategory.list');
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $category = Category::where('name', 'like', '%' . $search . '%')->paginate(10);
        return view('admin.category.listCategories', compact('category'));
    }

}
