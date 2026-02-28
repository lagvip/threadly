<?php


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ColorController as AdminColorController;

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

Route::prefix('color')->name('color.')->group(function () {
    Route::get('/', [AdminColorController::class, 'index'])->name('list');
    Route::get('/bin', [AdminColorController::class, 'bin'])->name('bin');
    Route::post('/{id}/restore', [AdminColorController::class, 'restore'])->name('restore');
    Route::post('/{id}/forceDelete', [AdminColorController::class, 'forceDelete'])->name('forceDelete');
    Route::post('/forceDeleteAll', [AdminColorController::class, 'forceDeleteAll'])->name('forceDeleteAll');

    Route::get('/detail/{id}', [AdminColorController::class, 'show'])->name('detail');

    Route::get('/add', [AdminColorController::class, 'create'])->name('add');
    Route::post('/store', [AdminColorController::class, 'store'])->name('store');

    Route::get('/edit/{id}', [AdminColorController::class, 'edit'])->name('edit');
    Route::put('/update{id}', [AdminColorController::class, 'update'])->name('update');

    Route::delete('/delete/{id}', [AdminColorController::class, 'destroy'])->name('delete');
    Route::get('/search', [AdminColorController::class, 'search'])->name('search');
});
