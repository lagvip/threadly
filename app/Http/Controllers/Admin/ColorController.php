<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Colors\IndexColorsRequest;
use App\Http\Requests\Admin\Colors\StoreColorRequest;
use App\Http\Requests\Admin\Colors\UpdateColorRequest;
use App\Models\Color;
use App\Services\Admin\Colors\AdminColorQueryService;
use App\Services\Admin\Colors\AdminColorService;
use RuntimeException;

class ColorController extends Controller
{
    public function __construct(
        protected AdminColorQueryService $queries,
        protected AdminColorService $colors
    ) {}

    public function index(IndexColorsRequest $request)
    {
        $this->authorize('viewAny', Color::class);

        return view('admin.color.listColors', $this->queries->indexData($request->keyword()));
    }

    public function create()
    {
        $this->authorize('create', Color::class);

        return view('admin.color.addColor');
    }

    public function store(StoreColorRequest $request)
    {
        $this->authorize('create', Color::class);

        $warning = $this->colors->create($request->validated());

        if ($warning) {
            return back()->withInput()->with('warning', $warning);
        }

        return redirect()->route('listColor.list')->with('success', 'Thêm màu thành công.');
    }

    public function show(int $id)
    {
        $this->authorize('viewAny', Color::class);

        return view('admin.color.detailColor', ['color' => $this->colors->find($id)]);
    }

    public function edit(int $id)
    {
        $this->authorize('updateAny', Color::class);

        return view('admin.color.updateColor', ['color' => $this->colors->find($id)]);
    }

    public function update(UpdateColorRequest $request, int $id)
    {
        $this->authorize('updateAny', Color::class);

        $warning = $this->colors->update($id, $request->validated());

        if ($warning) {
            return back()->withInput()->with('warning', $warning);
        }

        return redirect()->route('listColor.list')->with('success', 'Cập nhật màu thành công.');
    }

    public function destroy(int $id)
    {
        $this->authorize('deleteAny', Color::class);

        $this->colors->softDelete($id);

        return redirect()->route('listColor.list')->with('success', 'Đã chuyển màu vào thùng rác.');
    }

    public function search(IndexColorsRequest $request)
    {
        $this->authorize('viewAny', Color::class);

        $route = $request->get('from', 'list') === 'trash' ? 'listColor.bin' : 'listColor.list';

        return redirect()->route($route, ['keyword' => $request->keyword()]);
    }

    public function bin(IndexColorsRequest $request)
    {
        $this->authorize('viewAny', Color::class);

        return view('admin.color.bin', $this->queries->binData($request->keyword()));
    }

    public function restore(int $id)
    {
        $this->authorize('restore', Color::class);

        try {
            $this->colors->restore($id);

            return redirect()->route('listColor.bin')->with('success', 'Khôi phục màu thành công.');
        } catch (RuntimeException $e) {
            return redirect()->route('listColor.bin')->with('error', $e->getMessage());
        }
    }

    public function forceDelete(int $id)
    {
        $this->authorize('forceDelete', Color::class);

        try {
            $this->colors->forceDelete($id);

            return redirect()->route('listColor.bin')->with('success', 'Xóa vĩnh viễn màu thành công.');
        } catch (RuntimeException $e) {
            return redirect()->route('listColor.bin')->with('error', $e->getMessage());
        }
    }

    public function forceDeleteAll()
    {
        $this->authorize('forceDelete', Color::class);

        try {
            $this->colors->forceDeleteAll();

            return redirect()->route('listColor.bin')->with('success', 'Đã xóa vĩnh viễn toàn bộ màu trong thùng rác.');
        } catch (RuntimeException $e) {
            return redirect()->route('listColor.bin')->with('error', $e->getMessage());
        }
    }
}
