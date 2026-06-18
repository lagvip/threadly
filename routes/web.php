<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\InventoryReceiptController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderDetailController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\RefundRequestController as AdminRefundRequestController;
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
use App\Http\Controllers\Client\ChatController as ClientChatController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\ClientOrderController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Controllers\Client\RefundRequestController as ClientRefundRequestController;
use App\Http\Controllers\Client\WishlistController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

// =======================================================
// CLIENT
// =======================================================
// Redirect
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/admin', fn () => redirect('/admin/dashboard'));

Route::get('/ve-chung-toi', [HomeController::class, 'about'])->name('client.about');

// Client giao diện
Route::get('/san-pham/{id}', [ProductController::class, 'show'])->name('client.product.detail');
Route::get('/category/{id}', [CategoryController::class, 'show'])->name('client.category');
Route::get('/tim-kiem', [ProductController::class, 'search'])->name('client.products.search');
// Liên hệ
Route::get('/lien-he', [ContactController::class, 'index'])->name('contact.index');
Route::post('/lien-he', [ContactController::class, 'store'])->name('contact.store');

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
        Route::post('/{id}/reviews/{detailId}', [ClientOrderController::class, 'submitReview'])
            ->whereNumber('id')
            ->whereNumber('detailId')
            ->name('reviews.submit');
        Route::post('/{id}/confirm-received', [ClientOrderController::class, 'confirmReceived'])
            ->whereNumber('id')
            ->name('confirm-received');
    });

    // Hoàn tiền demo VNPay và ví demo
    Route::prefix('tai-khoan/hoan-tien')->name('client.refunds.')->group(function () {
        Route::get('/orders/{order}/create', [ClientRefundRequestController::class, 'create'])
            ->whereNumber('order')
            ->name('create');

        Route::post('/orders/{order}', [ClientRefundRequestController::class, 'store'])
            ->whereNumber('order')
            ->name('store');
    });

    Route::get('/tai-khoan/vi-demo', [ClientRefundRequestController::class, 'wallet'])
        ->name('client.wallet.index');

    // Địa chỉ
    Route::prefix('tai-khoan/so-dia-chi')->name('client.addresses.')->group(function () {
        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::post('/store', [AddressController::class, 'store'])->name('store');
        Route::put('/{id}', [AddressController::class, 'update'])->name('update');
        Route::delete('/{id}', [AddressController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/default', [AddressController::class, 'setDefault'])->name('default');
    });

    // Trợ lý AI
    Route::prefix('tro-ly-ai')->name('client.ai.')->group(function () {
        Route::get('/', [ChatbotController::class, 'index'])->name('index');
        Route::post('/hoi', [ChatbotController::class, 'ask'])->name('ask');
    });

    // ưa thích
    Route::prefix('yeu-thich')->name('client.wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/them', [WishlistController::class, 'store'])->name('store');
        Route::delete('/xoa/{id}', [WishlistController::class, 'destroy'])->name('destroy');

    });
    // Chat realtime
    Route::post('/chat/messages', [ClientChatController::class, 'send'])->name('client.chat.send');
    Route::get('/chat/widget-data', [ClientChatController::class, 'widgetData'])->name('client.chat.widgetData');

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
        // CHAT REALTIME
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/chats', [AdminChatController::class, 'index'])->name('chats.index');
            Route::get('/chats/{conversation}', [AdminChatController::class, 'show'])->name('chats.show');
            Route::post('/chats/{conversation}/messages', [AdminChatController::class, 'send'])->name('chats.send');
        });

        // CONTACTS
        Route::prefix('listContact')->name('listContact.')->group(function () {
            Route::get('/', [AdminContactController::class, 'index'])->name('list');
            Route::get('/{contact}', [AdminContactController::class, 'show'])->name('show');
            Route::post('/{contact}/toggle-replied', [AdminContactController::class, 'toggleReplied'])->name('toggleReplied');
        });

        // CATEGORY
        Route::prefix('listCategory')->name('listCategory.')->group(function () {
            Route::get('/trash', [AdminCategoryController::class, 'trash'])->name('trash');
            Route::post('/restore/{id}', [AdminCategoryController::class, 'restore'])->name('restore');

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
            Route::post('/restore/{id}', [BannerController::class, 'restore'])->name('restore');
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
            Route::post('/restore/{id}', [ColorController::class, 'restore'])->name('restore');
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
            Route::post('/restore/{id}', [SizeController::class, 'restore'])->name('restoreSize');
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
            Route::post('/restore/{id}', [BrandController::class, 'restore'])->name('restore');

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
            Route::delete('/delete/{id}', [AdminProductController::class, 'destroy'])->name('destroy'); // Xóa mềm
            Route::get('/trash', [AdminProductController::class, 'trash'])->name('trash');
            Route::post('/restore/{id}', [AdminProductController::class, 'restore'])->name('restore');
            Route::post('/bulk-delete', [AdminProductController::class, 'bulkDelete'])->name('bulkDelete');
            Route::post('/bulk-restore', [AdminProductController::class, 'bulkRestore'])->name('bulkRestore');
            Route::get('/search', [AdminProductController::class, 'search'])->name('search');

            Route::get('/variant-trash', [AdminProductController::class, 'variantTrash'])->name('variant.trash');
            Route::post('/variant-restore', [AdminProductController::class, 'variantRestore'])->name('variant.restore');

            Route::post('/{id}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('toggleStatus');
            Route::post('/variants/{id}/toggle-status', [AdminProductController::class, 'toggleVariantStatus'])->name('variant.toggleStatus');
        });

        // INVENTORY
        Route::prefix('admin/inventory')->name('admin.inventory.')->group(function () {
            Route::get('/products/search', [InventoryReceiptController::class, 'searchProducts'])->name('products.search');
            Route::get('/products/variants', [InventoryReceiptController::class, 'productVariants'])->name('products.variants');
            Route::get('/receipts', [InventoryReceiptController::class, 'index'])->name('receipts.index');
            Route::get('/receipts/create', [InventoryReceiptController::class, 'create'])->name('receipts.create');
            Route::post('/receipts', [InventoryReceiptController::class, 'store'])->name('receipts.store');
            Route::get('/receipts/{receipt}', [InventoryReceiptController::class, 'show'])->name('receipts.show');
            Route::post('/receipts/{receipt}/post', [InventoryReceiptController::class, 'post'])->name('receipts.post');
            Route::post('/receipts/{receipt}/cancel', [InventoryReceiptController::class, 'cancel'])->name('receipts.cancel');
            Route::get('/movements', [InventoryReceiptController::class, 'movements'])->name('movements.index');
        });

        // ORDER
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'destroy']);
        Route::get('/order-details', [OrderController::class, 'details'])->name('order.details');
        Route::get('/orders/{id}/print', [OrderController::class, 'print'])->name('orders.print');

        Route::post('/orders/{order}/ghn/create', [OrderController::class, 'createGhnOrder'])->name('orders.ghn.create');
        Route::post('/orders/{order}/ghn/sync', [OrderController::class, 'syncGhnOrder'])->name('orders.ghn.sync');
        Route::post('/orders/{order}/ghn/cancel', [OrderController::class, 'cancelGhnOrder'])->name('orders.ghn.cancel');
        Route::get('/orders/{order}/ghn/print', [OrderController::class, 'printGhnOrder'])->name('orders.ghn.print');
        Route::post('/orders/{order}/ghn/simulate/{status}', [OrderController::class, 'simulateGhnStatus'])
            ->name('orders.ghn.simulate');

        Route::resource('order-details', OrderDetailController::class)->only(['index', 'store', 'destroy']);
        Route::prefix('deleted')->name('deleted.')->group(function () {
            Route::get('/', [OrderController::class, 'trash'])->name('index');
            Route::post('/restore', [OrderController::class, 'restore'])->name('restore');
        });

        // REFUND REQUESTS / DEMO WALLET
        Route::prefix('refunds')->name('admin.refunds.')->group(function () {
            Route::get('/', [AdminRefundRequestController::class, 'index'])->name('index');
            Route::get('/{refundRequest}', [AdminRefundRequestController::class, 'show'])->name('show');
            Route::post('/{refundRequest}/approve', [AdminRefundRequestController::class, 'approve'])->name('approve');
            Route::post('/{refundRequest}/restock', [AdminRefundRequestController::class, 'restock'])->name('restock');
            Route::post('/{refundRequest}/reject', [AdminRefundRequestController::class, 'reject'])->name('reject');
        });

        // REVIEW
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [ReviewController::class, 'index'])->name('index');
            Route::get('/trash', [ReviewController::class, 'trash'])->name('trash');
            Route::get('/{review}/edit', [ReviewController::class, 'edit'])->name('edit');
            Route::put('/{review}', [ReviewController::class, 'update'])->name('update');
            Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
            Route::post('/restore/{id}', [ReviewController::class, 'restore'])->name('restore');
            Route::post('/bulk-restore', [ReviewController::class, 'bulkRestore'])->name('bulkRestore');
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
            Route::post('/restore/{id}', [UserController::class, 'restore'])->name('restore');
            Route::delete('/force-delete/{id}', [UserController::class, 'forceDelete'])->name('forceDelete');
            Route::patch('/{id}/ban', [UserController::class, 'ban'])->name('ban');
            Route::patch('/{id}/unban', [UserController::class, 'unban'])->name('unban');
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
            Route::post('/restore/{id}', [RoleController::class, 'restore'])->name('restore');
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
            Route::delete('/force-delete/{id}', [AdminProductController::class, 'forceDelete'])->name('forceDelete');
            Route::post('/variant-force-delete', [AdminProductController::class, 'variantForceDelete'])->name('variant.forceDelete');
        });

        // ORDER - FORCE DELETE
        Route::prefix('deleted')->name('deleted.')->group(function () {
            Route::post('/force-delete', [OrderController::class, 'forceDelete'])->name('forceDelete');
        });
        // REVIEW - FORCE DELETE
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::delete('/force-delete/{id}', [ReviewController::class, 'forceDelete'])->name('forceDelete');
        });
        // SIZE - FORCE DELETE
        Route::prefix('listSize')->name('listSize.')->group(function () {
            Route::delete('/force-delete/{id}', [SizeController::class, 'forceDelete'])->name('forceDeleteSize');
        });
    });
});
