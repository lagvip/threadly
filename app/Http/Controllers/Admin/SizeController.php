<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sizes\IndexSizesRequest;
use App\Http\Requests\Admin\Sizes\StoreSizeRequest;
use App\Http\Requests\Admin\Sizes\UpdateSizeRequest;
use App\Models\Size;
use App\Services\Admin\Sizes\AdminSizeQueryService;
use App\Services\Admin\Sizes\AdminSizeService;
use RuntimeException;

class SizeController extends Controller
{
    public function __construct(
        protected AdminSizeQueryService $queries,
        protected AdminSizeService $sizes
    ) {}

    public function index(IndexSizesRequest $request)
    {
        $this->authorize('viewAny', Size::class);

        return view('admin.size.index', $this->queries->indexData($request->keyword()));
    }

    public function trash(IndexSizesRequest $request)
    {
        $this->authorize('viewAny', Size::class);

        return view('admin.size.trash', $this->queries->trashData($request->keyword()));
    }

    public function create()
    {
        $this->authorize('create', Size::class);

        return view('admin.size.create');
    }

    public function store(StoreSizeRequest $request)
    {
        $this->authorize('create', Size::class);

        $warning = $this->sizes->create($request->validated());

        if ($warning) {
            return back()->withInput()->with('warning', $warning);
        }

        return redirect()->route('listSize.list')->with('success', 'Thêm size thành công');
    }

    public function show($id)
    {
        $this->authorize('viewAny', Size::class);

        return view('admin.size.detail', ['size' => $this->sizes->find((int) $id)]);
    }

    public function edit($id)
    {
        $this->authorize('updateAny', Size::class);

        return view('admin.size.edit', ['size' => $this->sizes->find((int) $id)]);
    }

    public function update(UpdateSizeRequest $request, $id)
    {
        $this->authorize('updateAny', Size::class);

        $warning = $this->sizes->update((int) $id, $request->validated());

        if ($warning) {
            return back()->withInput()->with('warning', $warning);
        }

        return redirect()->route('listSize.list')->with('success', 'Cập nhật size thành công');
    }

    public function destroy($id)
    {
        $this->authorize('deleteAny', Size::class);

        $this->sizes->softDelete((int) $id);

        return redirect()->route('listSize.list')->with('success', 'Đã chuyển size vào thùng rác');
    }

    public function restore($id)
    {
        $this->authorize('restore', Size::class);

        try {
            $this->sizes->restore((int) $id);

            return redirect()->route('listSize.trash')->with('success', 'Khôi phục size thành công');
        } catch (RuntimeException $e) {
            return redirect()->route('listSize.trash')->with('error', $e->getMessage());
        }
    }

    public function forceDelete($id)
    {
        $this->authorize('forceDelete', Size::class);

        try {
            $this->sizes->forceDelete((int) $id);

            return redirect()->route('listSize.trash')->with('success', 'Xóa vĩnh viễn size thành công');
        } catch (RuntimeException $e) {
            return redirect()->route('listSize.trash')->with('error', $e->getMessage());
        }
    }

    public function search(IndexSizesRequest $request)
    {
        $this->authorize('viewAny', Size::class);

        $route = $request->get('from', 'list') === 'trash' ? 'listSize.trash' : 'listSize.list';

        return redirect()->route($route, ['keyword' => $request->keyword()]);
    }
}
