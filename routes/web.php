<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminController; // đúng namespace Admin (A hoa)
use App\Http\Controllers\admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\SizeController;

/*
|--------------------------------------------------------------------------
| ONLY ADMIN + CATEGORY + PRODUCT (GIỮ NGUYÊN ROUTE NAME CŨ)
|--------------------------------------------------------------------------
| - /admin            -> redirect về /admin/dashboard
| - /admin/dashboard  -> homeAdmin (giữ name admin.homeAdmin)
| - product giữ nguyên: admin.product.listProduct, admin.product.create, ...
| - category giữ nguyên group listCategory.* (nếu view đang dùng)
|--------------------------------------------------------------------------
*/

// Trang chủ -> admin dashboard
Route::redirect('/', '/admin/dashboard');

// Gõ /admin -> vào dashboard luôn
Route::get('/admin', function () {
    return redirect('/admin/dashboard');
});

Route::prefix('admin')->name('admin.')->group(function () {

    // Dashboard (giữ y như route cũ)
    Route::get('/dashboard', [AdminController::class, 'homeAdmin'])->name('homeAdmin');

    /*
    |--------------------------------------------------------------------------
    | PRODUCT (GIỮ NGUYÊN y hệt route cũ)
    |--------------------------------------------------------------------------
    */
    

});

/*
|--------------------------------------------------------------------------
| CATEGORY (GIỮ NGUYÊN y hệt route cũ: listCategory.*)
|--------------------------------------------------------------------------
| Vì view/menu dự án bạn đang gọi listCategory.list, listCategory.addCategory...
|--------------------------------------------------------------------------
*/
Route::prefix('listCategory')->name('listCategory.')->group(function () {
    Route::get('/', [AdminCategoryController::class, 'index'])->name('list');

    Route::get('/detail/{id}', [AdminCategoryController::class, 'show'])->name('detailCategory');

    Route::get('/add', [AdminCategoryController::class, 'create'])->name('addCategory');
    Route::post('/store', [AdminCategoryController::class, 'store'])->name('storeCategory');

    Route::get('/edit/{id}', [AdminCategoryController::class, 'edit'])->name('editCategory');
    Route::put('/update{id}', [AdminCategoryController::class, 'update'])->name('updateCategory');

    Route::delete('/delete/{id}', [AdminCategoryController::class, 'destroy'])->name('deleteCategory');
    Route::get('/search', [AdminCategoryController::class, 'search'])->name('searchCategory');
});
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

