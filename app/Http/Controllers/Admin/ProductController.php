<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Products\BulkProductIdsRequest;
use App\Http\Requests\Admin\Products\IndexProductsRequest;
use App\Http\Requests\Admin\Products\StoreProductRequest;
use App\Http\Requests\Admin\Products\ToggleProductStatusRequest;
use App\Http\Requests\Admin\Products\UpdateProductRequest;
use App\Models\Product;
use App\Services\Admin\Products\AdminProductPageService;
use App\Services\Admin\Products\AdminProductVariantWriteService;
use App\Services\Admin\Products\AdminProductWriteService;
use RuntimeException;

class ProductController extends Controller
{
    public function __construct(
        protected AdminProductPageService $pages,
        protected AdminProductWriteService $products,
        protected AdminProductVariantWriteService $variants,
    ) {}

    public function list(IndexProductsRequest $request)
    {
        $this->authorize('viewAny', Product::class);

        return view('admin.product.list-product', $this->pages->indexData($request->filters()));
    }

    public function search(IndexProductsRequest $request)
    {
        return $this->list($request);
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        return view('admin.product.create-product', $this->pages->createData());
    }

    public function postCreate(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        try {
            $this->products->create($request->validated(), $request->file('variants', []));

            return redirect()->route('product.listProduct')->with('success', 'Thêm sản phẩm và biến thể thành công');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e instanceof RuntimeException ? $e->getMessage() : 'Thêm sản phẩm thất bại');
        }
    }

    public function edit($id)
    {
        $this->authorize('updateAny', Product::class);

        return view('admin.product.edit-product', $this->pages->editData((int) $id));
    }

    public function postEdit(UpdateProductRequest $request, $id)
    {
        $this->authorize('updateAny', Product::class);

        try {
            $this->products->update(
                $request->validated(),
                (int) $id,
                $request->file('variants', []),
                $request->file('variants_new', [])
            );

            return redirect()->route('product.listProduct')->with('success', 'Cập nhật sản phẩm thành công');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e instanceof RuntimeException ? $e->getMessage() : 'Cập nhật sản phẩm thất bại');
        }
    }

    public function detail($id)
    {
        $this->authorize('viewAny', Product::class);

        return view('admin.product.detail-product', $this->pages->detailData((int) $id));
    }

    public function show($id)
    {
        return $this->detail($id);
    }

    public function toggleStatus(ToggleProductStatusRequest $request, $id)
    {
        $this->authorize('updateAny', Product::class);

        try {
            $message = $this->products->updateProductStatus((int) $id, $request->statusValue());

            return redirect()->back()->with('success', $message);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function trash()
    {
        $this->authorize('viewAny', Product::class);

        return view('admin.product.trashProduct', $this->pages->trashData());
    }

    public function restore($id)
    {
        $this->authorize('restore', Product::class);

        try {
            $this->products->restore((int) $id);

            return redirect()->route('product.listProduct')->with('success', 'Khôi phục sản phẩm thành công');
        } catch (RuntimeException $e) {
            return redirect()->route('product.trash')->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorize('deleteAny', Product::class);

        try {
            $this->products->softDelete((int) $id);

            return redirect()->route('product.listProduct')->with('success', 'Xóa sản phẩm thành công');
        } catch (RuntimeException $e) {
            return redirect()->route('product.listProduct')->with('error', $e->getMessage());
        }
    }

    public function forceDelete($id)
    {
        $this->authorize('forceDelete', Product::class);

        try {
            $this->products->forceDelete((int) $id);

            return redirect()->route('product.trash')->with('success', 'Xóa vĩnh viễn sản phẩm thành công');
        } catch (RuntimeException $e) {
            return redirect()->route('product.trash')->with('error', $e->getMessage());
        }
    }

    public function bulkDelete(BulkProductIdsRequest $request)
    {
        $this->authorize('deleteAny', Product::class);

        try {
            $this->products->bulkDelete($request->ids());

            return redirect()->route('product.listProduct')->with('success', 'Xóa hàng loạt thành công');
        } catch (RuntimeException $e) {
            return redirect()->route('product.listProduct')->with('error', $e->getMessage());
        }
    }

    public function bulkRestore(BulkProductIdsRequest $request)
    {
        $this->authorize('restore', Product::class);

        try {
            $this->products->bulkRestore($request->ids());

            return redirect()->route('product.listProduct')->with('success', 'Khôi phục hàng loạt thành công');
        } catch (RuntimeException $e) {
            return redirect()->route('product.trash')->with('error', $e->getMessage());
        }
    }

    public function variantTrash()
    {
        $this->authorize('manageVariants', Product::class);

        return view('admin.product.variant-trash', $this->pages->variantTrashData());
    }

    public function toggleVariantStatus(ToggleProductStatusRequest $request, $id)
    {
        $this->authorize('manageVariants', Product::class);

        try {
            $message = $this->variants->updateStatus((int) $id, $request->statusValue());

            return redirect()->back()->with('success', $message);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function variantRestore(BulkProductIdsRequest $request)
    {
        $this->authorize('manageVariants', Product::class);

        try {
            $this->variants->bulkRestore($request->ids());

            return redirect()->route('product.variant.trash')->with('success', 'Khôi phục biến thể thành công');
        } catch (RuntimeException $e) {
            return redirect()->route('product.variant.trash')->with('error', $e->getMessage());
        }
    }

    public function variantForceDelete(BulkProductIdsRequest $request)
    {
        $this->authorize('forceDelete', Product::class);

        $redirect = redirect()->route('product.variant.trash');

        foreach ($this->variants->bulkForceDelete($request->ids()) as $type => $message) {
            $redirect->with($type, $message);
        }

        return $redirect;
    }
}
