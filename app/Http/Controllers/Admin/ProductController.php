<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected $productService;
    protected $productVariantService;

    public function __construct(ProductService $productService, ProductVariantService $productVariantService)
    {
        $this->productService = $productService;
        $this->productVariantService = $productVariantService;
    }

    public function list()
    {
        $products = $this->productService->getAllProducts();
        return view('admin.product.list-product', compact('products'));
    }

    public function create()
    {
        $brands = Brand::all();
        $categories = Category::with('parent')
            ->whereNotNull('id_parent')
            ->get();
        $colors = Color::all();
        $sizes = Size::all();

        return view('admin.product.create-product', compact('brands', 'categories', 'colors', 'sizes'));
    }

    public function postCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:250|unique:products,name',
            'description' => 'nullable|string',
            'id_brand' => 'required|exists:brands,id',
            'id_category' => 'required|exists:categories,id',
            'image_primary' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'status' => 'required|in:active,inactive',

            'variants' => 'required|array|min:1',
            'variants.*.id_color' => 'required|exists:colors,id',
            'variants.*.id_size' => 'required|exists:sizes,id',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.quantity' => 'nullable|integer|min:0',
            'variants.*.image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $combinations = [];
        foreach ($request->variants as $variant) {
            $key = $variant['id_color'] . '-' . $variant['id_size'];

            if (in_array($key, $combinations)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Biến thể bị trùng màu sắc và kích cỡ');
            }

            $combinations[] = $key;
        }

        DB::beginTransaction();

        try {
            $product = $this->productService->createProduct($request->all());

            if (!$product) {
                throw new \Exception('Không thể tạo sản phẩm');
            }

            foreach ($request->variants as $index => $variant) {
                $variant['id_product'] = $product->id;
                $variant['price'] = $variant['price'] ?? 0;
                $variant['quantity'] = $variant['quantity'] ?? 0;
                $variant['status'] = $variant['status'] ?? 'active';

                if ($request->hasFile("variants.$index.image")) {
                    $variant['image'] = $request->file("variants.$index.image");
                }

                $created = $this->productVariantService->createProductVariant($variant);

                if (!$created) {
                    throw new \Exception('Không thể tạo biến thể sản phẩm');
                }
            }

            DB::commit();

            return redirect()->route('product.listProduct')
                ->with('success', 'Thêm sản phẩm và biến thể thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi thêm sản phẩm: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Thêm sản phẩm thất bại');
        }
    }

    public function edit($id)
    {
        $product = $this->productService->getProductById($id);
        $brands = Brand::all();
        $categories = Category::with('parent')
            ->whereNotNull('id_parent')
            ->get();
        $colors = Color::all();
        $sizes = Size::all();

        return view('admin.product.edit-product', compact('product', 'brands', 'categories', 'colors', 'sizes'));
    }

    public function postEdit(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:250|unique:products,name,' . $id,
            'description' => 'nullable|string',
            'id_brand' => 'required|exists:brands,id',
            'id_category' => 'required|exists:categories,id',
            'image_primary' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'status' => 'required|in:active,inactive',

            'variants_new' => 'nullable|array',
            'variants_new.*.id_color' => 'nullable|exists:colors,id',
            'variants_new.*.id_size' => 'nullable|exists:sizes,id',
            'variants_new.*.price' => 'nullable|numeric|min:0',
            'variants_new.*.quantity' => 'nullable|integer|min:0',
            'variants_new.*.image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $product = $this->productService->updateProduct($request->all(), $id);

        if ($product) {
            return redirect()->route('product.listProduct')->with('success', 'Cập nhật sản phẩm thành công');
        }

        return redirect()->back()->withInput()->with('error', 'Cập nhật sản phẩm thất bại');
    }

    public function detail($id)
    {
        $product = $this->productService->getProductById($id);
        $brands = Brand::all();
        $categories = Category::with('parent')
            ->whereNotNull('id_parent')
            ->get();
        return view('admin.product.detail-product', compact('product', 'brands', 'categories'));
    }

    public function trash()
    {
        $trashedProducts = $this->productService->getTrashedProducts();
        return view('admin.product.trashProduct', compact('trashedProducts'));
    }

    public function restore($id)
    {
        if ($this->productService->restoreProduct($id)) {
            return redirect()->route('product.listProduct')->with('success', 'Khôi phục sản phẩm thành công');
        }
        return redirect()->route('product.trash')->with('error', 'Khôi phục sản phẩm thất bại');
    }

    public function destroy($id)
    {
        if ($this->productService->deleteProduct($id)) {
            return redirect()->route('product.listProduct')->with('success', 'Xóa sản phẩm thành công');
        }
        return redirect()->route('product.listProduct')->with('error', 'Xóa sản phẩm thất bại');
    }

    public function forceDelete($id)
    {
        if ($this->productService->delete($id)) {
            return redirect()->route('product.trash')->with('success', 'Xóa vĩnh viễn sản phẩm thành công');
        }
        return redirect()->route('product.trash')->with('error', 'Xóa vĩnh viễn sản phẩm thất bại');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->route('product.trash')->with('error', 'Chưa chọn sản phẩm nào');
        }
        if ($this->productService->bulkDelete($ids)) {
            return redirect()->route('product.listProduct')->with('success', 'Xóa hàng loạt thành công');
        }
        return redirect()->route('product.listProduct')->with('error', 'Xóa hàng loạt thất bại');
    }

    public function bulkRestore(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->route('product.trash')->with('error', 'Chưa chọn sản phẩm nào');
        }
        if ($this->productService->bulkRestore($ids)) {
            return redirect()->route('product.listProduct')->with('success', 'Khôi phục hàng loạt thành công');
        }
        return redirect()->route('product.trash')->with('error', 'Khôi phục hàng loạt thất bại');
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('search');

        $products = Product::with(['brand', 'category'])
            ->when(!empty($searchTerm), function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('brand', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('category', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    });
            })->paginate(10);

        $search = $searchTerm;

        if ($request->ajax()) {
            return view('admin.product.components.product-table', compact('products'))->render();
        }

        return view('admin.product.list-product', compact('products', 'search'));
    }

    public function variantTrash()
    {
        $trashedVariants = $this->productVariantService->getTrashedProductVariants();
        return view('admin.product.variant-trash', compact('trashedVariants'));
    }

    public function variantRestore(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->route('product.variant.trash')->with('error', 'Chưa chọn biến thể nào');
        }

        if ($this->productVariantService->bulkRestore($ids)) {
            return redirect()->route('product.variant.trash')->with('success', 'Khôi phục biến thể thành công');
        }

        return redirect()->route('product.variant.trash')->with('error', 'Khôi phục biến thể thất bại');
    }

    public function variantForceDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->route('product.variant.trash')->with('error', 'Chưa chọn biến thể nào');
        }

        $success = [];
        $failed = [];

        foreach ($ids as $id) {
            if ($this->productVariantService->forceDeleteProductVariant($id)) {
                $success[] = $id;
            } else {
                $failed[] = $id;
            }
        }

        if (!empty($success) && empty($failed)) {
            return redirect()->route('product.variant.trash')
                ->with('success', 'Đã xoá vĩnh viễn các biến thể đã chọn');
        } elseif (!empty($success) && !empty($failed)) {
            return redirect()->route('product.variant.trash')
                ->with('success', 'Một số biến thể đã được xoá vĩnh viễn')
                ->with('error', 'Một số biến thể không thể xoá do đã có trong đơn hàng');
        } else {
            return redirect()->route('product.variant.trash')
                ->with('error', 'Không thể xoá vĩnh viễn biến thể nào (đã có trong đơn hàng)');
        }
    }
}
