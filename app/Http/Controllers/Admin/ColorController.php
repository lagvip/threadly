<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Colors\IndexColorsRequest;
use App\Http\Requests\Admin\Colors\StoreColorRequest;
use App\Http\Requests\Admin\Colors\UpdateColorRequest;
use App\Services\Admin\Colors\AdminColorQueryService;
use App\Services\Admin\Colors\AdminColorService;
use RuntimeException;

class ColorController extends Controller
{
    public function __construct(
        protected AdminColorQueryService $queries,
        protected AdminColorService $colors
    ) {
    }

    public function index(IndexColorsRequest $request)
    {
        return view('admin.color.listColors', $this->queries->indexData($request->keyword()));
    }

    public function create()
    {
        return view('admin.color.addColor');
    }

    public function store(StoreColorRequest $request)
    {
        $warning = $this->colors->create($request->validated());

        if ($warning) {
            return back()->withInput()->with('warning', $warning);
        }

        return redirect()->route('listColor.list')->with('success', 'Thêm màu thành công.');
    }

    public function show($id)
    {
        return view('admin.color.detailColor', ['color' => $this->colors->find((int) $id)]);
    }

    public function edit($id)
    {
        return view('admin.color.updateColor', ['color' => $this->colors->find((int) $id)]);
    }

    public function update(UpdateColorRequest $request, $id)
    {
        $warning = $this->colors->update((int) $id, $request->validated());

        if ($warning) {
            return back()->withInput()->with('warning', $warning);
        }

        return redirect()->route('listColor.list')->with('success', 'Cập nhật màu thành công.');
    }

    public function destroy($id)
    {
        $this->colors->softDelete((int) $id);

        return redirect()->route('listColor.list')->with('success', 'Đã chuyển màu vào thùng rác.');
    }

    public function search(IndexColorsRequest $request)
    {
        $route = $request->get('from', 'list') === 'trash' ? 'listColor.bin' : 'listColor.list';

        return redirect()->route($route, ['keyword' => $request->keyword()]);
    }

    public function bin(IndexColorsRequest $request)
    {
        return view('admin.color.bin', $this->queries->binData($request->keyword()));
    }

    public function restore($id)
    {
        try {
            $this->colors->restore((int) $id);

            return redirect()->route('listColor.bin')->with('success', 'Khôi phục màu thành công.');
        } catch (RuntimeException $e) {
            return redirect()->route('listColor.bin')->with('error', $e->getMessage());
        }
    }

    public function forceDelete($id)
    {
        try {
            $this->colors->forceDelete((int) $id);

            return redirect()->route('listColor.bin')->with('success', 'Xóa vĩnh viễn màu thành công.');
        } catch (RuntimeException $e) {
            return redirect()->route('listColor.bin')->with('error', $e->getMessage());
        }
    }

    public function forceDeleteAll()
    {
        try {
            $this->colors->forceDeleteAll();

            return redirect()->route('listColor.bin')->with('success', 'Đã xóa vĩnh viễn toàn bộ màu trong thùng rác.');
        } catch (RuntimeException $e) {
            return redirect()->route('listColor.bin')->with('error', $e->getMessage());
        }
    }
}
