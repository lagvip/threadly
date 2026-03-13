<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ColorController as AdminColorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderDetailController;
use App\Http\Controllers\Admin\ReviewController;
// Redirect
Route::redirect('/', '/admin/dashboard');
Route::get('/admin', fn () => redirect('/admin/dashboard'));

//
// LOGIN
//
Route::prefix('admin')->name('admin.')->middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'postLoginAdmin'])->name('auth.postLoginAdmin');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/register', [AuthController::class, 'postRegister'])->name('auth.postRegister');

});
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

//
// BỌC TẤT CẢ ROUTE CẦN ĐĂNG NHẬP BẰNG auth
//
Route::middleware(['auth'])->group(function () {

    // DASHBOARD
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'homeAdmin'])->name('homeAdmin');
    });

    // CATEGORY
    Route::prefix('listCategory')->name('listCategory.')->group(function () {
        Route::get('/', [AdminCategoryController::class, 'index'])->name('list');
        Route::get('/detail/{id}', [AdminCategoryController::class, 'show'])->name('detailCategory');
        Route::get('/add', [AdminCategoryController::class, 'create'])->name('addCategory');
        Route::post('/store', [AdminCategoryController::class, 'store'])->name('storeCategory');
        Route::get('/edit/{id}', [AdminCategoryController::class, 'edit'])->name('editCategory');
        Route::put('/update/{id}', [AdminCategoryController::class, 'update'])->name('updateCategory');
        Route::delete('/delete/{id}', [AdminCategoryController::class, 'destroy'])->name('deleteCategory');
        Route::get('/search', [AdminCategoryController::class, 'search'])->name('searchCategory');
    });

    // BANNER
    Route::prefix('listBanner')->name('listBanner.')->group(function () {
        Route::get('/', [BannerController::class, 'index'])->name('list');
        Route::get('/trash', [BannerController::class, 'trash'])->name('trash');
        Route::get('/restore/{id}', [BannerController::class, 'restore'])->name('restore');
        Route::get('/detail/{id}', [BannerController::class, 'show'])->name('detailBanner');
        Route::get('/add', [BannerController::class, 'create'])->name('addBanner');
        Route::post('/store', [BannerController::class, 'store'])->name('storeBanner');
        Route::get('/edit/{id}', [BannerController::class, 'edit'])->name('editBanner');
        Route::put('/update/{id}', [BannerController::class, 'update'])->name('updateBanner');
        Route::delete('/delete/{id}', [BannerController::class, 'destroy'])->name('deleteBanner');
        Route::get('/search', [BannerController::class, 'search'])->name('searchBanner');
    });

    // COLOR
    Route::prefix('listColor')->name('listColor.')->group(function () {
        Route::get('/', [AdminColorController::class, 'index'])->name('list');
        Route::get('/detail/{id}', [AdminColorController::class, 'show'])->name('detailColor');
        Route::get('/add', [AdminColorController::class, 'create'])->name('addColor');
        Route::post('/store', [AdminColorController::class, 'store'])->name('storeColor');
        Route::get('/edit/{id}', [AdminColorController::class, 'edit'])->name('editColor');
        Route::put('/update/{id}', [AdminColorController::class, 'update'])->name('updateColor');
        Route::delete('/delete/{id}', [AdminColorController::class, 'destroy'])->name('deleteColor');
        Route::get('/search', [AdminColorController::class, 'search'])->name('searchColor');
    });

    // USERS
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('list');
        Route::get('/detail/{id}', [UserController::class, 'show'])->name('detail');
        Route::get('/add', [UserController::class, 'create'])->name('add');
        Route::post('/store', [UserController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [UserController::class, 'destroy'])->name('delete');
        Route::get('/search', [UserController::class, 'search'])->name('search');

        Route::post('/{id}/assign-role', [UserController::class, 'assignRole'])->name('assignRole');
    });
    // SIZE
    Route::prefix('listSize')->name('listSize.')->group(function () {
    Route::get('/', [SizeController::class, 'index'])->name('list');
    Route::get('/detail/{id}', [SizeController::class, 'show'])->name('detailSize');
    Route::get('/add', [SizeController::class, 'create'])->name('addSize');
    Route::post('/store', [SizeController::class, 'store'])->name('storeSize');
    Route::get('/edit/{id}', [SizeController::class, 'edit'])->name('editSize');
    Route::put('/update/{id}', [SizeController::class, 'update'])->name('updateSize');
    Route::delete('/delete/{id}', [SizeController::class, 'destroy'])->name('deleteSize');
    Route::get('/search', [SizeController::class, 'search'])->name('searchSize');
    });
     // VOUCHERS
    Route::resource('vouchers', VoucherController::class);
    //BRAND
    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');

    Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');

    Route::delete('/brands/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');

    Route::get('/brands/{id}/edit', [BrandController::class, 'edit'])->name('brands.edit');
    Route::put('/brands/{id}', [BrandController::class, 'update'])->name('brands.update');
    // PRODUCT
    Route::prefix('product')->name('product.')->group(function () {
        Route::get('/list-product', [AdminProductController::class, 'list'])->name('listProduct');
        Route::get('/add', [AdminProductController::class, 'create'])->name('create');
        Route::post('/postCreate', [AdminProductController::class, 'postCreate'])->name('postCreate');
        Route::get('/edit/{id}', [AdminProductController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [AdminProductController::class, 'postEdit'])->name('postEdit');
        Route::get('/detail/{id}', [AdminProductController::class, 'detail'])->name('detail');
        Route::get('/show/{id}', [AdminProductController::class, 'show'])->name('show');
        Route::get('/delete/{id}', [AdminProductController::class, 'destroy'])->name('destroy');
        Route::get('/trash', [AdminProductController::class, 'trash'])->name('trash');
        Route::get('/restore/{id}', [AdminProductController::class, 'restore'])->name('restore');
        Route::get('/force-delete/{id}', [AdminProductController::class, 'forceDelete'])->name('forceDelete');
        Route::post('/bulk-delete', [AdminProductController::class, 'bulkDelete'])->name('bulkDelete');
        Route::post('/bulk-restore', [AdminProductController::class, 'bulkRestore'])->name('bulkRestore');
        // phần search
        Route::get('/search', [AdminProductController::class, 'search'])->name('search');
        // biến thể
        Route::get('/variant-trash', [AdminProductController::class, 'variantTrash'])->name('variant.trash');
    Route::post('/variant-restore', [AdminProductController::class, 'variantRestore'])->name('variant.restore');
    Route::post('/variant-force-delete',[AdminProductController::class, 'variantForceDelete'])->name('variant.forceDelete');
    });
    // ORDER
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
    Route::get('/order-details', [OrderController::class, 'details'])->name('order.details');
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('/orders/{id}/refund', [OrderController::class, 'refund'])->name('orders.refund');
    Route::get('/orders/{id}/print', [OrderController::class, 'print'])->name('orders.print');

    Route::resource('order-details', OrderDetailController::class)->only(['index', 'store', 'destroy']);

    Route::prefix('deleted')->name('deleted.')->group(function () {
        Route::get('/', [OrderController::class, 'trash'])->name('index');
        Route::post('/restore', [OrderController::class, 'restore'])->name('restore');
        Route::post('/force-delete', [OrderController::class, 'forceDelete'])->name('forceDelete');
    });
    // REVIEW
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::get('/{review}/edit', [ReviewController::class, 'edit'])->name('edit');
        Route::put('/{review}', [ReviewController::class, 'update'])->name('update');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });
});


