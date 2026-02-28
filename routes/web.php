<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminController; // đúng namespace Admin (A hoa)
use App\Http\Controllers\admin\CategoryController as AdminCategoryController;



// Trang chủ -> admin dashboard
Route::redirect('/', '/admin/dashboard');

// Gõ /admin -> vào dashboard luôn
Route::get('/admin', function () {
    return redirect('/admin/dashboard');
});

Route::prefix('admin')->name('admin.')->group(function () {

   
    Route::get('/dashboard', [AdminController::class, 'homeAdmin'])->name('homeAdmin');

     Route::prefix('shipping')->name('shipping.')->controller(ShippingController::class)->group(function() {
        Route::get('/', 'index')->name('index');         // -> Tên: admin.shipping.index
        Route::post('/store', 'store')->name('store');   // -> Tên: admin.shipping.store
        Route::get('/edit/{id}', 'edit')->name('edit');  // -> Tên: admin.shipping.edit
        Route::put('/update/{id}', 'update')->name('update'); // -> Tên: admin.shipping.update
        Route::get('/delete/{id}', 'destroy')->name('delete'); // -> Tên: admin.shipping.delete
        Route::patch('/update-status/{id}', 'updateStatus')->name('updateStatus');
    });
    

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
