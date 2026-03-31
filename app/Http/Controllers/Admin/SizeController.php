<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->get('keyword', ''));

        $sizes = Size::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.size.index', compact('sizes', 'keyword'));
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->get('keyword', ''));

        $sizes = Size::onlyTrashed()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.size.trash', compact('sizes', 'keyword'));
    }

    public function create()
    {
        return view('admin.size.create');
    }

    public function store(Request $request)
    {
        $name = trim((string) $request->name);
        $request->merge(['name' => $name]);

        $request->validate([
            'name' => [
                'required',
                'regex:/^[0-9]+$/',
                'max:255',
                Rule::unique('sizes', 'name')
                    ->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ], [
            'name.required' => 'Tên size không được để trống',
            'name.regex' => 'Size chỉ được nhập số',
            'name.max' => 'Tên size tối đa 255 ký tự',
            'name.unique' => 'Size này đã tồn tại',
        ]);

        $trashedDuplicate = Size::onlyTrashed()
            ->where('name', $name)
            ->first();

        if ($trashedDuplicate) {
            return back()
                ->withInput()
                ->with('warning', 'Size này đang nằm trong thùng rác. Hãy khôi phục thay vì tạo mới.');
        }

        Size::create([
            'name' => $name,
        ]);

        return redirect()
            ->route('listSize.list')
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
        $size = Size::findOrFail($id);
        $name = trim((string) $request->name);
        $request->merge(['name' => $name]);

        $request->validate([
            'name' => [
                'required',
                'regex:/^[0-9]+$/',
                'max:255',
                Rule::unique('sizes', 'name')
                    ->ignore($size->id)
                    ->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ], [
            'name.required' => 'Tên size không được để trống',
            'name.regex' => 'Size chỉ được nhập số',
            'name.max' => 'Tên size tối đa 255 ký tự',
            'name.unique' => 'Size này đã tồn tại',
        ]);

        $trashedDuplicate = Size::onlyTrashed()
            ->where('name', $name)
            ->where('id', '!=', $size->id)
            ->first();

        if ($trashedDuplicate) {
            return back()
                ->withInput()
                ->with('warning', 'Đã có một size cùng tên trong thùng rác. Hãy khôi phục hoặc xóa vĩnh viễn bản cũ trước.');
        }

        $size->update([
            'name' => $name,
        ]);

        return redirect()
            ->route('listSize.list')
            ->with('success', 'Cập nhật size thành công');
    }

    public function destroy($id)
    {
        $size = Size::findOrFail($id);
        $size->delete();

        return redirect()
            ->route('listSize.list')
            ->with('success', 'Đã chuyển size vào thùng rác');
    }

    public function restore($id)
    {
        $size = Size::onlyTrashed()->findOrFail($id);

        $activeDuplicate = Size::where('name', $size->name)->first();
        if ($activeDuplicate) {
            return redirect()
                ->route('listSize.trash')
                ->with('error', 'Không thể khôi phục vì đã có size cùng tên đang hoạt động.');
        }

        $size->restore();

        return redirect()
            ->route('listSize.trash')
            ->with('success', 'Khôi phục size thành công');
    }

    public function forceDelete($id)
    {
        $size = Size::onlyTrashed()->findOrFail($id);

        $variantUsageCount = DB::table('product_variants')
            ->where('id_size', $size->id)
            ->count();

        if ($variantUsageCount > 0) {
            return redirect()
                ->route('listSize.trash')
                ->with('error', 'Không thể xóa vĩnh viễn vì size này vẫn đang được dùng trong biến thể sản phẩm.');
        }

        $size->forceDelete();

        return redirect()
            ->route('listSize.trash')
            ->with('success', 'Xóa vĩnh viễn size thành công');
    }

    public function search(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $from = $request->get('from', 'list');

        return $from === 'trash'
            ? redirect()->route('listSize.trash', ['keyword' => $keyword])
            : redirect()->route('listSize.list', ['keyword' => $keyword]);
    }
}
