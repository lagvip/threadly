<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

// class SizeController extends Controller
class SizeController extends Controller
{
    public function index()
    {
        $sizes = Size::latest()->paginate(10);
        return view('admin.size.index', compact('sizes'));
    }

    public function create()
    {
        return view('admin.size.create');
    }

   public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255|unique:sizes,name'
    ], [
        'name.required' => 'Tên size không được để trống',
        'name.max' => 'Tên size tối đa 255 ký tự',
        'name.unique' => 'Size này đã tồn tại'
    ]);

    Size::create([
        'name' => $request->name
    ]);

    return redirect()->route('listSize.list')
                     ->with('success', 'Thêm size thành công');
}
    public function show($id)
    {
        $size = Size::findOrFail($id);
        return view('admin.size.detail', compact('size'));
    }

    public function edit($id)
    {
        $size = Size::findOrFail($id);
        return view('admin.size.edit', compact('size'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $size = Size::findOrFail($id);
        $size->update([
            'name' => $request->name
        ]);

        return redirect()->route('listSize.list')
                         ->with('success', 'Cập nhật thành công');
    }

    public function destroy($id)
    {
        $size = Size::findOrFail($id);
        $size->delete();

        return redirect()->route('listSize.list')
                         ->with('success', 'Xóa thành công');
    }

    public function search(Request $request)
    {
        $sizes = Size::where('name', 'like', '%'.$request->keyword.'%')
                     ->paginate(10);

        return view('admin.size.index', compact('sizes'));
    }
}