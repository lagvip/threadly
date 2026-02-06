<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BannerController;


Route::redirect('/', '/admin/dashboard');


Route::get('/admin', function () {
    return redirect('/admin/dashboard');
});

Route::prefix('admin')->name('admin.')->group(function () {


    Route::get('/dashboard', [AdminController::class, 'homeAdmin'])->name('homeAdmin');




});


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

Route::prefix('listBanner')->name('listBanner.')->group(function () {
    Route::get('/', [BannerController::class, 'index'])->name('list');

    Route::get('/detail/{id}', [BannerController::class, 'show'])->name('detailBanner');

    Route::get('/add', [BannerController::class, 'create'])->name('addBanner');
    Route::post('/store', [BannerController::class, 'store'])->name('storeBanner');

    Route::get('/edit/{id}', [BannerController::class, 'edit'])->name('editBanner');
    Route::put('/update{id}', [BannerController::class, 'update'])->name('updateBanner');

    Route::delete('/delete/{id}', [BannerController::class, 'destroy'])->name('deleteBanner');
    Route::get('/search', [BannerController::class, 'search'])->name('searchBanner');
});
