<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::query()->latest('id')->paginate(10);
        return view('admin.banner.listBanners', compact('banners'));
    }

    public function trash()
    {
        $banners = Banner::onlyTrashed()->latest('id')->paginate(10);
        return view('admin.banner.trashBanners', compact('banners'));
    }

    public function restore(string $id)
    {
        $banner = Banner::onlyTrashed()->findOrFail($id);
        $banner->restore();

        return redirect()->route('listBanner.trash')->with('success', 'Khôi phục thành công');
    }

    public function create()
    {
        return view('admin.banner.addBanner');
    }

    public function store(StoreBannerRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banner', 'public');
        }

        $data['is_active'] = $request->boolean('is_active');

        Banner::create($data);

        return redirect()->route('listBanner.list')->with('success', 'Thêm thành công');
    }

    public function show(string $id)
    {
        $banner = Banner::findOrFail($id);
        return view('admin.banner.detailBanner', compact('banner'));
    }

    public function edit(string $id)
    {
        $banner = Banner::findOrFail($id);
        return view('admin.banner.updateBanner', compact('banner'));
    }

    public function update(UpdateBannerRequest $request, string $id)
    {
        $banner = Banner::findOrFail($id);

        $data = $request->validated();
        unset($data['image']);

        $currentImage = $banner->image;
        $newImagePath = null;

        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('banner', 'public');
            $data["image"] = $newImagePath;
        }

        $data['is_active'] = $request->boolean('is_active');

        $is_update = $banner->update($data);

        if ($is_update && $newImagePath) {
            if ($currentImage && Storage::disk('public')->exists($currentImage)) {
                Storage::disk('public')->delete($currentImage);
            }
        }

        if ($is_update) {
            return redirect()->route("listBanner.list")->with("success", "Cập nhật thành công!");
        } else {
            return redirect()->route("listBanner.list")->with("error", "Cập nhật không thành công!");
        }
    }

    public function destroy(string $id)
    {
        $banner = Banner::findOrFail($id);

        $banner->delete();
        return redirect()->route('listBanner.list')->with('success', 'Xóa thành công');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return redirect()->route('listBanner.list')->with('error', 'Chưa chọn banner nào để xoá');
        }

        $ids = array_values(array_filter($ids, fn($id) => is_numeric($id)));
        if (empty($ids)) {
            return redirect()->route('listBanner.list')->with('error', 'Danh sách banner không hợp lệ');
        }

        Banner::whereIn('id', $ids)->delete();

        return redirect()->route('listBanner.list')->with('success', 'Đã xoá các banner đã chọn');
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $banners = Banner::where('title', 'like', '%' . $search . '%')->paginate(10);
        return view('admin.banner.listBanners', compact('banners'));
    }
}
