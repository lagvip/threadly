<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderDetailController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\AddressController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\CategoryController;
use App\Http\Controllers\Client\ChatbotController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\ClientOrderController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\ProductController;
use Illuminate\Support\Facades\Route;

// =======================================================
// CLIENT
// =======================================================
// Redirect
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/admin', fn () => redirect('/admin/dashboard'));

// Client giao diện
Route::get('/san-pham/{id}', [ProductController::class, 'show'])->name('client.product.detail');
Route::get('/category/{id}', [CategoryController::class, 'show'])->name('client.category');

// Client cần đăng nhập - giỏ hàng
Route::middleware('auth')->group(function () {
    Route::get('/gio-hang', [CartController::class, 'index'])->name('client.cart.index');
    Route::post('/gio-hang/them', [CartController::class, 'add'])->name('client.cart.add');
    Route::post('/gio-hang/cap-nhat', [CartController::class, 'update'])->name('client.cart.update');
    Route::delete('/gio-hang/xoa/{id}', [CartController::class, 'remove'])->name('client.cart.remove');
    Route::post('/checkout/select-items', [CheckoutController::class, 'selectItems'])->name('client.checkout.selectItems');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('client.checkout.index');
    Route::post('/checkout/shipping-fee', [CheckoutController::class, 'getShippingFee'])->name('client.checkout.shipping-fee');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('client.checkout.store');

    Route::get('/checkout/ghn/provinces', [CheckoutController::class, 'getProvinces'])->name('client.checkout.ghn.provinces');
    Route::get('/checkout/ghn/districts', [CheckoutController::class, 'getDistricts'])->name('client.checkout.ghn.districts');
    Route::get('/checkout/ghn/wards', [CheckoutController::class, 'getWards'])->name('client.checkout.ghn.wards');
    Route::post('/checkout/address/store', [CheckoutController::class, 'storeAddress'])->name('client.checkout.address.store');
    Route::post('/checkout/buy-now', [CheckoutController::class, 'buyNow'])->name('client.checkout.buyNow');

    Route::post('/orders/{id}/reorder', [CheckoutController::class, 'reorder'])->name('client.orders.reorder');
    Route::post('/orders/{id}/repay-vnpay', [CheckoutController::class, 'repayVnpay'])->name('client.orders.repay-vnpay');
    Route::post('/checkout/voucher/apply', [CheckoutController::class, 'applyVoucher'])->name('client.checkout.voucher.apply');
    Route::post('/checkout/voucher/remove', [CheckoutController::class, 'removeVoucher'])->name('client.checkout.voucher.remove');

    // Tài khoản
    Route::prefix('tai-khoan')->name('client.account.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('/thong-tin', [AccountController::class, 'detail'])->name('detail');
        Route::post('/cap-nhat', [AccountController::class, 'update'])->name('update');
    });

    // Đơn hàng
    Route::prefix('tai-khoan/don-hang')->name('client.orders.')->group(function () {
        Route::get('/', [ClientOrderController::class, 'index'])->name('index');
        Route::get('/{id}', [ClientOrderController::class, 'show'])->name('show');
        Route::post('/{id}/cancel', [ClientOrderController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/reviews/{productId}', [ClientOrderController::class, 'submitReview'])
            ->whereNumber('id')
            ->whereNumber('productId')
            ->name('reviews.submit');
    });

    // Địa chỉ
    Route::prefix('tai-khoan/so-dia-chi')->name('client.addresses.')->group(function () {
        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::post('/store', [AddressController::class, 'store'])->name('store');
        Route::put('/{id}', [AddressController::class, 'update'])->name('update');
        Route::delete('/{id}', [AddressController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/default', [AddressController::class, 'setDefault'])->name('default');
    });

});

// VNPay callback routes: để ngoài auth
Route::get('/checkout/vnpay/return', [CheckoutController::class, 'paymentReturn'])->name('client.checkout.vnpay-return');
Route::get('/checkout/vnpay/ipn', [CheckoutController::class, 'paymentIpn'])->name('client.checkout.vnpay-ipn');


// =======================================================
// AUTH
// =======================================================

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'postLogin'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'postRegister'])->name('register.submit');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendOtp'])->name('password.email');
    Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPasswordWithOtp'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('password.change.form');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.change');
});

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');


// =======================================================
// TẤT CẢ ROUTE CẦN ĐĂNG NHẬP
// =======================================================

Route::middleware(['auth'])->group(function () {

    // =======================================================
    // NHÓM ADMIN + MANAGER
    // - Được vào admin panel
    // - Được CRUD thường
    // - Được xóa mềm / khôi phục
    // - KHÔNG được xóa cứng
    // =======================================================
    Route::middleware(['role:admin,manager'])->group(function () {

        // DASHBOARD
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'homeAdmin'])->name('homeAdmin');
        });

        // CATEGORY
        Route::prefix('listCategory')->name('listCategory.')->group(function () {
            Route::get('/trash', [AdminCategoryController::class, 'trash'])->name('trash');
            Route::get('/restore/{id}', [AdminCategoryController::class, 'restore'])->name('restore');

            Route::get('/', [AdminCategoryController::class, 'index'])->name('list');
            Route::get('/add', [AdminCategoryController::class, 'create'])->name('addCategory');
            Route::post('/store', [AdminCategoryController::class, 'store'])->name('storeCategory');
            Route::get('/detail/{id}', [AdminCategoryController::class, 'show'])->name('detailCategory');
            Route::get('/edit/{id}', [AdminCategoryController::class, 'edit'])->name('editCategory');
            Route::put('/update/{id}', [AdminCategoryController::class, 'update'])->name('updateCategory');
            Route::delete('/delete/{id}', [AdminCategoryController::class, 'destroy'])->name('deleteCategory'); // Xóa mềm
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
            Route::delete('/bulk-delete', [BannerController::class, 'bulkDestroy'])->name('bulkDelete');
            Route::delete('/delete/{id}', [BannerController::class, 'destroy'])->name('deleteBanner');
            Route::get('/search', [BannerController::class, 'search'])->name('searchBanner');
        });

        // COLOR
        Route::prefix('listColor')->name('listColor.')->group(function () {
            Route::get('/', [ColorController::class, 'index'])->name('list');
            Route::get('/bin', [ColorController::class, 'bin'])->name('bin');
            Route::get('/detail/{id}', [ColorController::class, 'show'])->name('detail');
            Route::get('/add', [ColorController::class, 'create'])->name('add');
            Route::post('/store', [ColorController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [ColorController::class, 'edit'])->name('edit');
            Route::put('/update/{id}', [ColorController::class, 'update'])->name('update');
            Route::delete('/delete/{id}', [ColorController::class, 'destroy'])->name('delete');
            Route::get('/restore/{id}', [ColorController::class, 'restore'])->name('restore');
            Route::get('/search', [ColorController::class, 'search'])->name('search');
        });

        // SIZE
       Route::prefix('listSize')->name('listSize.')->group(function () {
            Route::get('/', [SizeController::class, 'index'])->name('list');
            Route::get('/trash', [SizeController::class, 'trash'])->name('trash');
            Route::get('/detail/{id}', [SizeController::class, 'show'])->name('detailSize');
            Route::get('/add', [SizeController::class, 'create'])->name('addSize');
            Route::post('/store', [SizeController::class, 'store'])->name('storeSize');
            Route::get('/edit/{id}', [SizeController::class, 'edit'])->name('editSize');
            Route::put('/update/{id}', [SizeController::class, 'update'])->name('updateSize');
            Route::delete('/delete/{id}', [SizeController::class, 'destroy'])->name('deleteSize');
            Route::get('/restore/{id}', [SizeController::class, 'restore'])->name('restoreSize');
            Route::get('/search', [SizeController::class, 'search'])->name('searchSize');
        });

        // VOUCHER
        Route::prefix('admin')->group(function () {
            Route::resource('vouchers', VoucherController::class);
            Route::get('vouchers-trashed', [VoucherController::class, 'trashed'])->name('vouchers.trashed');
            Route::post('vouchers/{voucher}/restore', [VoucherController::class, 'restore'])->name('vouchers.restore');
        });

        // BRAND
        Route::prefix('brands')->name('brands.')->group(function () {
            Route::get('/trash', [BrandController::class, 'trash'])->name('trash');
            Route::get('/restore/{id}', [BrandController::class, 'restore'])->name('restore');

            Route::get('/', [BrandController::class, 'index'])->name('index');
            Route::get('/create', [BrandController::class, 'create'])->name('create');
            Route::post('/', [BrandController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [BrandController::class, 'edit'])->name('edit');
            Route::put('/{id}', [BrandController::class, 'update'])->name('update');
            Route::delete('/{id}', [BrandController::class, 'destroy'])->name('destroy');
        });

        // PRODUCT
        Route::prefix('product')->name('product.')->group(function () {
            Route::get('/list-product', [AdminProductController::class, 'list'])->name('listProduct');
            Route::get('/add', [AdminProductController::class, 'create'])->name('create');
            Route::post('/postCreate', [AdminProductController::class, 'postCreate'])->name('postCreate');
            Route::get('/edit/{id}', [AdminProductController::class, 'edit'])->name('edit');
            Route::post('/update/{id}', [AdminProductController::class, 'postEdit'])->name('postEdit');
            Route::get('/detail/{id}', [AdminProductController::class, 'detail'])->name('detail');
            Route::get('/show/{id}', [AdminProductController::class, 'show'])->name('show');
            Route::get('/delete/{id}', [AdminProductController::class, 'destroy'])->name('destroy'); // Xóa mềm
            Route::get('/trash', [AdminProductController::class, 'trash'])->name('trash');
            Route::get('/restore/{id}', [AdminProductController::class, 'restore'])->name('restore');
            Route::post('/bulk-delete', [AdminProductController::class, 'bulkDelete'])->name('bulkDelete');
            Route::post('/bulk-restore', [AdminProductController::class, 'bulkRestore'])->name('bulkRestore');
            Route::get('/search', [AdminProductController::class, 'search'])->name('search');

            Route::get('/variant-trash', [AdminProductController::class, 'variantTrash'])->name('variant.trash');
            Route::post('/variant-restore', [AdminProductController::class, 'variantRestore'])->name('variant.restore');

            Route::post('/{id}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('toggleStatus');
            Route::post('/variants/{id}/toggle-status', [AdminProductController::class, 'toggleVariantStatus'])->name('variant.toggleStatus');
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
        });

        // REVIEW
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [ReviewController::class, 'index'])->name('index');
            Route::get('/{review}/edit', [ReviewController::class, 'edit'])->name('edit');
            Route::put('/{review}', [ReviewController::class, 'update'])->name('update');
        });
        // Trợ lý AI
        Route::prefix('tro-ly-ai')->name('client.ai.')->group(function () {
        Route::get('/', [ChatbotController::class, 'index'])->name('index');
        Route::post('/hoi', [ChatbotController::class, 'ask'])->name('ask');
    });
    });

    // =======================================================
    // NHÓM CHỈ ADMIN
    // - Quản lý user
    // - Quản lý role
    // - Tất cả thao tác xóa cứng
    // =======================================================
    Route::middleware(['role:admin'])->group(function () {

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
            Route::get('/trash', [UserController::class, 'trash'])->name('trash');
            Route::get('/restore/{id}', [UserController::class, 'restore'])->name('restore');
            Route::delete('/force-delete/{id}', [UserController::class, 'forceDelete'])->name('forceDelete');
        });

        // ROLE
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('list');
            Route::get('/trash', [RoleController::class, 'trash'])->name('trash');
            Route::get('/detail/{id}', [RoleController::class, 'show'])->name('detail');
            Route::get('/add', [RoleController::class, 'create'])->name('add');
            Route::post('/store', [RoleController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [RoleController::class, 'edit'])->name('edit');
            Route::put('/update/{id}', [RoleController::class, 'update'])->name('update');
            Route::delete('/delete/{id}', [RoleController::class, 'destroy'])->name('delete');
            Route::get('/restore/{id}', [RoleController::class, 'restore'])->name('restore');
            Route::delete('/force-delete/{id}', [RoleController::class, 'forceDelete'])->name('forceDelete');
        });

        // CATEGORY - FORCE DELETE
        Route::prefix('listCategory')->name('listCategory.')->group(function () {
            Route::delete('/force-delete/{id}', [AdminCategoryController::class, 'forceDelete'])->name('forceDelete');
        });

        // COLOR - FORCE DELETE
        Route::prefix('listColor')->name('listColor.')->group(function () {
            Route::delete('/force-delete/{id}', [ColorController::class, 'forceDelete'])->name('forceDelete');
            Route::delete('/force-delete-all', [ColorController::class, 'forceDeleteAll'])->name('forceDeleteAll');
        });

        // VOUCHER - FORCE DELETE
        Route::prefix('admin')->group(function () {
            Route::delete('vouchers/{voucher}/force-delete', [VoucherController::class, 'forceDelete'])->name('vouchers.forceDelete');
        });

        // BRAND - FORCE DELETE
        Route::prefix('brands')->name('brands.')->group(function () {
            Route::delete('/force-delete/{id}', [BrandController::class, 'forceDelete'])->name('forceDelete');
        });

        // PRODUCT - FORCE DELETE
        Route::prefix('product')->name('product.')->group(function () {
            Route::get('/force-delete/{id}', [AdminProductController::class, 'forceDelete'])->name('forceDelete');
            Route::post('/variant-force-delete', [AdminProductController::class, 'variantForceDelete'])->name('variant.forceDelete');
        });

        // ORDER - FORCE DELETE
        Route::prefix('deleted')->name('deleted.')->group(function () {
            Route::post('/force-delete', [OrderController::class, 'forceDelete'])->name('forceDelete');
        });
        // REVIEW - FORCE DELETE
         Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
        });
        // SIZE - FORCE DELETE
        Route::prefix('listSize')->name('listSize.')->group(function () {
            Route::delete('/force-delete/{id}', [SizeController::class, 'forceDelete'])->name('forceDeleteSize');
        });
    });
});
