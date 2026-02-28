<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ColorController as AdminColorController;
use App\Http\Controllers\Admin\UserController;

// Redirect
Route::redirect('/', '/admin/dashboard');
Route::get('/admin', fn () => redirect('/admin/dashboard'));

// ======================
// AUTH (KHÔNG BỌC auth)
// ======================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'postLoginAdmin'])->name('auth.postLoginAdmin');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/register', [AuthController::class, 'postRegister'])->name('auth.postRegister');

    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

// =========================================================
// BỌC TẤT CẢ ROUTE CẦN ĐĂNG NHẬP BẰNG auth
// =========================================================
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

});
