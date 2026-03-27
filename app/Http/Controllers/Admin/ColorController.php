<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->get('keyword', ''));

        $colors = Color::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('code', 'like', '%' . $keyword . '%');
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.color.listColors', compact('colors', 'keyword'));
    }

    public function create()
    {
        return view('admin.color.addColor');
    }

    public function store(Request $request)
    {
        $name = trim((string) $request->name);
        $code = strtoupper(trim((string) $request->code));
        $request->merge([
            'name' => $name,
            'code' => $code,
        ]);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('colors', 'name')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('colors', 'code')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ], [
            'name.required' => 'Tên màu không được để trống.',
            'name.max' => 'Tên màu tối đa 255 ký tự.',
            'name.unique' => 'Tên màu này đã tồn tại.',
            'code.required' => 'Mã màu không được để trống.',
            'code.max' => 'Mã màu tối đa 255 ký tự.',
            'code.unique' => 'Mã màu này đã tồn tại.',
        ]);

        $trashedDuplicate = Color::onlyTrashed()
            ->where(function ($query) use ($name, $code) {
                $query->where('name', $name)
                    ->orWhere('code', $code);
            })
            ->first();

        if ($trashedDuplicate) {
            return back()
                ->withInput()
                ->with('warning', 'Màu này hoặc mã màu này đang nằm trong thùng rác. Hãy khôi phục thay vì tạo mới.');
        }

        Color::create([
            'name' => $name,
            'code' => $code,
        ]);

        return redirect()
            ->route('listColor.list')
            ->with('success', 'Thêm màu thành công.');
    }

    public function show($id)
    {
        $color = Color::findOrFail($id);
        return view('admin.color.detailColor', compact('color'));
    }

    public function edit($id)
    {
        $color = Color::findOrFail($id);
        return view('admin.color.updateColor', compact('color'));
    }

    public function update(Request $request, $id)
    {
        $color = Color::findOrFail($id);
        $name = trim((string) $request->name);
        $code = strtoupper(trim((string) $request->code));
        $request->merge([
            'name' => $name,
            'code' => $code,
        ]);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('colors', 'name')
                    ->ignore($color->id)
                    ->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('colors', 'code')
                    ->ignore($color->id)
                    ->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ], [
            'name.required' => 'Tên màu không được để trống.',
            'name.max' => 'Tên màu tối đa 255 ký tự.',
            'name.unique' => 'Tên màu này đã tồn tại.',
            'code.required' => 'Mã màu không được để trống.',
            'code.max' => 'Mã màu tối đa 255 ký tự.',
            'code.unique' => 'Mã màu này đã tồn tại.',
        ]);

        $trashedDuplicate = Color::onlyTrashed()
            ->where('id', '!=', $color->id)
            ->where(function ($query) use ($name, $code) {
                $query->where('name', $name)
                    ->orWhere('code', $code);
            })
            ->first();

        if ($trashedDuplicate) {
            return back()
                ->withInput()
                ->with('warning', 'Đã có một màu hoặc mã màu trùng trong thùng rác. Hãy xử lý bản cũ trước.');
        }

        $color->update([
            'name' => $name,
            'code' => $code,
        ]);

        return redirect()
            ->route('listColor.list')
            ->with('success', 'Cập nhật màu thành công.');
    }

    public function destroy($id)
    {
        $color = Color::findOrFail($id);
        $color->delete();

        return redirect()
            ->route('listColor.list')
            ->with('success', 'Đã chuyển màu vào thùng rác.');
    }

    public function search(Request $request)
    {
        $keyword = trim((string) $request->get('keyword', ''));
        $from = $request->get('from', 'list');

        return $from === 'trash'
            ? redirect()->route('listColor.bin', ['keyword' => $keyword])
            : redirect()->route('listColor.list', ['keyword' => $keyword]);
    }

    public function bin(Request $request)
    {
        $keyword = trim((string) $request->get('keyword', ''));

        $colors = Color::onlyTrashed()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('code', 'like', '%' . $keyword . '%');
                });
            })
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.color.bin', compact('colors', 'keyword'));
    }

    public function restore($id)
    {
        $color = Color::onlyTrashed()->findOrFail($id);

        $activeDuplicate = Color::query()
            ->where(function ($query) use ($color) {
                $query->where('name', $color->name)
                    ->orWhere('code', $color->code);
            })
            ->first();

        if ($activeDuplicate) {
            return redirect()
                ->route('listColor.bin')
                ->with('error', 'Không thể khôi phục vì đã có màu hoặc mã màu trùng đang hoạt động.');
        }

        $color->restore();

        return redirect()
            ->route('listColor.bin')
            ->with('success', 'Khôi phục màu thành công.');
    }

    public function forceDelete($id)
    {
        $color = Color::onlyTrashed()->findOrFail($id);

        $variantUsageCount = DB::table('product_variants')
            ->where('id_color', $color->id)
            ->count();

        if ($variantUsageCount > 0) {
            return redirect()
                ->route('listColor.bin')
                ->with('error', 'Không thể xóa vĩnh viễn vì màu này vẫn đang được dùng trong biến thể sản phẩm.');
        }

        $color->forceDelete();

        return redirect()
            ->route('listColor.bin')
            ->with('success', 'Xóa vĩnh viễn màu thành công.');
    }

    public function forceDeleteAll()
    {
        $blockedCount = Color::onlyTrashed()
            ->whereIn('id', function ($query) {
                $query->select('id_color')
                    ->from('product_variants')
                    ->whereNotNull('id_color');
            })
            ->count();

        if ($blockedCount > 0) {
            return redirect()
                ->route('listColor.bin')
                ->with('error', 'Có màu trong thùng rác vẫn đang được dùng trong biến thể sản phẩm, không thể xóa tất cả.');
        }

        Color::onlyTrashed()->forceDelete();

        return redirect()
            ->route('listColor.bin')
            ->with('success', 'Đã xóa vĩnh viễn toàn bộ màu trong thùng rác.');
    }
}
