<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Banners\BulkBannerIdsRequest;
use App\Http\Requests\Admin\Banners\SearchBannerRequest;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Models\Banner;
use App\Services\Admin\Banners\AdminBannerService;
use RuntimeException;

class BannerController extends Controller
{
    public function __construct(protected AdminBannerService $banners) {}

    public function index()
    {
        $this->authorize('viewAny', Banner::class);

        return view('admin.banner.listBanners', $this->banners->indexData());
    }

    public function trash()
    {
        $this->authorize('viewAny', Banner::class);

        return view('admin.banner.trashBanners', $this->banners->trashData());
    }

    public function restore(string $id)
    {
        $this->authorize('restore', Banner::class);

        $this->banners->restore((int) $id);

        return redirect()->route('listBanner.trash')->with('success', 'Khôi phục thành công');
    }

    public function create()
    {
        $this->authorize('create', Banner::class);

        return view('admin.banner.addBanner');
    }

    public function store(StoreBannerRequest $request)
    {
        $this->authorize('create', Banner::class);

        $this->banners->create($request->validated(), $request->file('image'), $request->boolean('is_active'));

        return redirect()->route('listBanner.list')->with('success', 'Thêm thành công');
    }

    public function show(string $id)
    {
        $this->authorize('viewAny', Banner::class);

        return view('admin.banner.detailBanner', ['banner' => $this->banners->find((int) $id)]);
    }

    public function edit(string $id)
    {
        $this->authorize('updateAny', Banner::class);

        return view('admin.banner.updateBanner', ['banner' => $this->banners->find((int) $id)]);
    }

    public function update(UpdateBannerRequest $request, string $id)
    {
        $this->authorize('updateAny', Banner::class);

        try {
            $this->banners->update((int) $id, $request->validated(), $request->file('image'), $request->boolean('is_active'));

            return redirect()->route('listBanner.list')->with('success', 'Cập nhật thành công!');
        } catch (RuntimeException $e) {
            return redirect()->route('listBanner.list')->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $this->authorize('deleteAny', Banner::class);

        $this->banners->softDelete((int) $id);

        return redirect()->route('listBanner.list')->with('success', 'Xóa thành công');
    }

    public function bulkDestroy(BulkBannerIdsRequest $request)
    {
        $this->authorize('deleteAny', Banner::class);

        try {
            $this->banners->bulkDelete($request->ids());

            return redirect()->route('listBanner.list')->with('success', 'Đã xóa các banner đã chọn');
        } catch (RuntimeException $e) {
            return redirect()->route('listBanner.list')->with('error', $e->getMessage());
        }
    }

    public function search(SearchBannerRequest $request)
    {
        $this->authorize('viewAny', Banner::class);

        return view('admin.banner.listBanners', $this->banners->indexData($request->keyword()));
    }
}
