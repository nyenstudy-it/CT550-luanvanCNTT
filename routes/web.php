<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CategoryProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ReviewController;
use \App\Http\Controllers\CommentController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminReviewController;
use Illuminate\Support\Facades\Route;
use Termwind\Components\Raw;

// Trang chủ khách hàng
Route::get('/', [HomeController::class, 'index'])->name('pages.home');
Route::get('/trangchu', [HomeController::class, 'index'])->name('pages.trangchu');
Route::get('/categories/{id}', [HomeController::class, 'showCategory'])->name('categories.show');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart', [CartController::class, 'list'])->name('cart.list');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/discount/save', [CartController::class, 'saveDiscount'])->name('cart.save_discount');
Route::post('/cart/discount', [CartController::class, 'applyDiscount'])->name('cart.apply_discount');
Route::get('/my-discounts', [DiscountController::class, 'customerIndex'])->name('discounts');
Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [CustomerAuthController::class, 'register']);
Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [CustomerAuthController::class, 'login']);
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
Route::get('/search-products', [ProductController::class, 'search'])->name('products.search');
Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');


// quên mk
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
    ->name('password.email');

Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])
    ->name('password.update');


Route::middleware(['auth', 'role:customer'])->group(function () {

    Route::get('/profile', [CustomerAuthController::class, 'profile'])->name('customer.profile');
    Route::post('/profile/update', [CustomerAuthController::class, 'profileUpdate'])->name('customer.profile.update');

    Route::get('/checkout', [CheckoutController::class, 'index'])
        ->name('checkout');

    Route::post('/checkout/store', [CheckoutController::class, 'store'])
        ->name('checkout.store');

    Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('orders.my');

    Route::get('/order/{id}', [OrderController::class, 'orderDetail'])->name('orders.detail');

    Route::post('/order/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/order-success/{id}', [OrderController::class, 'success'])->name('orders.success');

    Route::post('/order/{id}/received', [OrderController::class, 'confirmReceived'])->name('orders.received');

    Route::get('/payment/status/{order}', [PaymentController::class, 'status'])
        ->name('payment.status');

    Route::get('/payment/momo/{orderId}', [PaymentController::class, 'momo'])
        ->name('momo.payment');

    Route::post('/payment/momo-process/{orderId}', [PaymentController::class, 'momoProcess'])
        ->name('momo.process');

    Route::get('/payment/momo-return', [PaymentController::class, 'momoReturn'])
        ->name('momo.return');
    Route::get('/momo/pay/{orderId}', [PaymentController::class, 'momo'])
        ->name('momo.pay');

    Route::get('/payment/vnpay/{orderId}', [PaymentController::class, 'vnpay'])
        ->name('vnpay.payment');

    Route::get('/payment/vnpay-return', [PaymentController::class, 'vnpayReturn'])
        ->name('vnpay.return');

    Route::get('/vnpay/pay/{orderId}', [PaymentController::class, 'vnpay'])
        ->name('vnpay.pay');

    Route::post('/order/{id}/refund-request', [OrderController::class, 'requestRefund'])
        ->middleware('auth');

    Route::post('/order/{id}/refund', [OrderController::class, 'requestRefund'])
        ->name('orders.refund');


    Route::get('/notifications', [NotificationController::class, 'customerIndex'])->name('customer.notifications');
    Route::get('/notifications/read/{id}', [NotificationController::class, 'read'])->name('customer.notifications.read');
    Route::post('/notifications/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])
        ->name('customer.notifications.markAsRead');

    Route::get('product/{product}/review/{order?}', [ReviewController::class, 'reviewForm'])->name('reviews.form');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/{review}/like', [\App\Http\Controllers\ReviewLikeController::class, 'toggle'])->name('reviews.like');
    // Route reply của khách đã bị xóa — chỉ admin mới được phép reply (giống Shopee)
});

//Đăng ký ADMIN/STAFF (chỉ admin)
Route::get('/admin/register', [AdminController::class, 'register'])
    ->middleware(['auth', 'role:admin'])
    ->name('admin.register');


//AUTH ADMIN / STAFF 
Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->name('admin.authenticate');
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');


//Route quản lý nhân viên chỉ dành cho admin
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/staff', [AdminController::class, 'staffManagement'])
            ->name('admin.staff.list');
        Route::get('/staff/create', [AdminController::class, 'staffCreate'])
            ->name('admin.staff.create');
        Route::post('/staff/store', [AdminController::class, 'staffStore'])
            ->name('admin.staff.store');
        Route::get('/staff/edit/{id}', [AdminController::class, 'staffEdit'])
            ->name('admin.staff.edit');
        Route::post('/staff/update/{id}', [AdminController::class, 'staffUpdate'])
            ->name('admin.staff.update');
        Route::delete('/staff/destroy/{id}', [AdminController::class, 'staffDestroy'])
            ->name('admin.staff.destroy');
        Route::post('/staff/{id}/lock', [AdminController::class, 'staffLock'])
            ->name('admin.staff.lock');
        Route::post('/staff/{id}/unlock', [AdminController::class, 'staffUnlock'])
            ->name('admin.staff.unlock');

        Route::get(
            '/attendances/pending',
            [AttendanceController::class, 'pending']
        )->name('admin.attendances.pending');
        Route::post(
            '/attendances/{attendance}/approve-late',
            [AttendanceController::class, 'approveLate']
        )->name('admin.attendances.approveLate');
        Route::post(
            '/attendances/{attendance}/reject-late',
            [AttendanceController::class, 'rejectLate']
        )->name('admin.attendances.rejectLate');
        Route::post(
            '/attendances/{attendance}/approve-early',
            [AttendanceController::class, 'approveEarly']
        )->name('admin.attendances.approveEarly');
        Route::post(
            '/attendances/{attendance}/reject-early',
            [AttendanceController::class, 'rejectEarly']
        )->name('admin.attendances.rejectEarly');
        Route::get('/attendances', [AttendanceController::class, 'index'])
            ->name('admin.attendances.index');
        Route::get('/attendances/create', [AttendanceController::class, 'create'])
            ->name('admin.attendances.create');
        Route::post('/attendances', [AttendanceController::class, 'store'])
            ->name('admin.attendances.store');
        Route::get('/attendances/{attendance}/edit', [AttendanceController::class, 'edit'])
            ->name('admin.attendances.edit');
        Route::post('/attendances/{attendance}', [AttendanceController::class, 'update'])
            ->name('admin.attendances.update');
        Route::delete('/attendances/{attendance}', [AttendanceController::class, 'destroy'])
            ->name('admin.attendances.destroy');
        Route::get(
            '/salaries',
            [SalaryController::class, 'index']
        )->name('admin.salaries.index');
        Route::get(
            '/salaries/calculate/{staffId}/{month}/{year}',
            [SalaryController::class, 'calculateMonthly']
        )->name('admin.salaries.calculate');
    });

//  ADMIN + TẤT CẢ STAFF (dashboard, profile, thông báo)
Route::middleware(['auth', 'role:admin,staff'])
    ->prefix('admin')
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard');

        Route::get('/profile', [AdminController::class, 'profile'])->name('profile.show');
        Route::post('/profile/update', [AdminController::class, 'profileUpdate'])->name('profile.update');

        Route::get('/notifications', [NotificationController::class, 'adminIndex'])->name('admin.notifications');
        Route::get('/notifications/read/{id}', [NotificationController::class, 'read'])->name('admin.notifications.read');
    });

// ADMIN + THU NGÂN (cashier) Doanh thu
Route::middleware(['auth', 'can.position:cashier'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/revenue-statistics', [DashboardController::class, 'revenueStatistics'])
            ->name('admin.revenue.stats');
        Route::get('/revenue-statistics/export-excel', [DashboardController::class, 'exportRevenueExcel'])
            ->name('admin.revenue.export.excel');
        Route::get('/revenue-statistics/export-pdf', [DashboardController::class, 'exportRevenuePdf'])
            ->name('admin.revenue.export.pdf');
    });

//ADMIN + NHÂN VIÊN KHO (warehouse) Nhà phân phối, Danh mục, Sản phẩm, Kho hàng
Route::middleware(['auth', 'can.position:warehouse'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('admin.suppliers.list');
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('admin.suppliers.create');
        Route::post('/suppliers/store', [SupplierController::class, 'store'])->name('admin.suppliers.store');
        Route::get('/suppliers/edit/{id}', [SupplierController::class, 'edit'])->name('admin.suppliers.edit');
        Route::post('/suppliers/update/{id}', [SupplierController::class, 'update'])->name('admin.suppliers.update');
        Route::delete('/suppliers/destroy/{id}', [SupplierController::class, 'destroy'])->name('admin.suppliers.destroy');

        Route::get('/categories', [CategoryProductController::class, 'list'])->name('admin.categories.list');
        Route::get('/categories/create', [CategoryProductController::class, 'create'])->name('admin.categories.create');
        Route::get('/categories/edit/{id}', [CategoryProductController::class, 'edit'])->name('admin.categories.edit');
        Route::post('/categories/store', [CategoryProductController::class, 'store'])->name('admin.categories.store');
        Route::post('/categories/update/{id}', [CategoryProductController::class, 'update'])->name('admin.categories.update');
        Route::delete('/categories/destroy/{id}', [CategoryProductController::class, 'destroy'])->name('admin.categories.destroy');

        Route::get('/products', [ProductController::class, 'list'])->name('admin.products.list');
        Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
        Route::post('/products/store', [ProductController::class, 'store'])->name('admin.products.store');
        Route::get('/products/edit/{id}', [ProductController::class, 'edit'])->name('admin.products.edit');
        Route::post('/products/update/{id}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/products/destroy/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
        Route::delete('/products/images/{id}', [ProductController::class, 'deleteImage'])->name('admin.products.images.delete');
        Route::get('/products/{id}/popup', [ProductController::class, 'showPopup'])->name('admin.products.popup');

        Route::get('/products/{productId}/variants', [ProductVariantController::class, 'index'])
            ->name('admin.products.variants.index');
        Route::get('/products/{productId}/variants/create', [ProductVariantController::class, 'create'])
            ->name('admin.products.variants.create');
        Route::get('/products/variants/{id}/edit', [ProductVariantController::class, 'edit'])
            ->name('admin.products.variants.edit');
        Route::post('/products/variants/{id}/update', [ProductVariantController::class, 'update'])
            ->name('admin.products.variants.update');
        Route::post('/products/{productId}/variants/store', [ProductVariantController::class, 'store'])
            ->name('admin.products.variants.store');
        Route::delete('/products/variants/{id}/destroy', [ProductVariantController::class, 'destroy'])
            ->name('admin.products.variants.destroy');

        Route::get('/imports', [ImportController::class, 'list'])->name('admin.imports.list');
        Route::get('/imports/create', [ImportController::class, 'create'])->name('admin.imports.create');
        Route::post('/imports/store', [ImportController::class, 'store'])->name('admin.imports.store');
        Route::get('/imports/{id}', [ImportController::class, 'show'])->name('admin.imports.show');
        Route::get('/imports/{id}/print', [ImportController::class, 'print'])->name('admin.imports.print');
        Route::get('/imports/get-products/{supplierId}', [ImportController::class, 'getProductsBySupplier']);
        Route::get('/imports/get-variants/{productId}', [ImportController::class, 'getVariantsByProduct']);

        Route::get('/inventories', [InventoryController::class, 'list'])->name('admin.inventories.list');
        Route::get('/inventories/{variantId}/batches', [InventoryController::class, 'batchPopup'])
            ->name('admin.inventories.batches');
    });

// ADMIN + THU NGÂN + NHÂN VIÊN XỬ LÝ ĐƠN (cashier, order_staff) Khách hàng, Đơn hàng
Route::middleware(['auth', 'can.position:cashier,order_staff'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/customers', [CustomerController::class, 'list'])
            ->name('admin.customers.list');
        Route::get('/customers/{id}', [CustomerController::class, 'show'])
            ->name('admin.customers.show');
        Route::post('/customers/{id}/lock', [CustomerController::class, 'lock'])
            ->name('admin.customers.lock');
        Route::post('/customers/{id}/unlock', [CustomerController::class, 'unlock'])
            ->name('admin.customers.unlock');
        Route::get('/customers/search', [CustomerController::class, 'search'])
            ->name('admin.customers.search');
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])
            ->name('admin.customers.destroy');

        Route::get('/orders', [AdminOrderController::class, 'index'])
            ->name('admin.orders');
        Route::get('/orders/{id}', [AdminOrderController::class, 'show'])
            ->name('admin.orders.detail');
        Route::post('/orders/update-status/{id}', [AdminOrderController::class, 'updateStatus'])
            ->name('admin.orders.updateStatus');
        Route::post('/orders/cancel/{id}', [AdminOrderController::class, 'cancel'])
            ->name('admin.orders.cancel');
        Route::post('/orders/{id}/approve-refund', [AdminOrderController::class, 'approveRefund'])
            ->name('admin.orders.approveRefund');
        Route::post('/orders/{id}/reject-refund', [AdminOrderController::class, 'rejectRefund'])
            ->name('admin.orders.rejectRefund');
    });

// ADMIN + NHÂN VIÊN XỬ LÝ ĐƠN (order_staff) Mã giảm giá, Đánh giá
Route::middleware(['auth', 'can.position:order_staff'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('admin.reviews');
        Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('admin.reviews.approve');
        Route::post('/reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('admin.reviews.reject');
        Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('admin.reviews.destroy');
        Route::get('/reviews/{review}/replies', [AdminReviewController::class, 'replies'])->name('admin.reviews.replies');
        Route::post('/reviews/{review}/reply', [AdminReviewController::class, 'reply'])->name('admin.reviews.reply');

        Route::get('/discounts', [DiscountController::class, 'index'])->name('admin.discounts.index');
        Route::get('/discounts/create', [DiscountController::class, 'create'])->name('admin.discounts.create');
        Route::post('/discounts', [DiscountController::class, 'store'])->name('admin.discounts.store');
        Route::get('/discounts/{discount}/edit', [DiscountController::class, 'edit'])->name('admin.discounts.edit');
        Route::post('/discounts/{discount}', [DiscountController::class, 'update'])->name('admin.discounts.update');
        Route::delete('/discounts/{discount}', [DiscountController::class, 'destroy'])->name('admin.discounts.destroy');
    });

// ADMIN + THU NGÂN (cashier) Blog
Route::middleware(['auth', 'can.position:cashier'])
    ->prefix('admin')
    ->group(function () {
        Route::get('blogs', [BlogController::class, 'adminIndex'])->name('admin.blogs.index');
        Route::get('blogs/create', [BlogController::class, 'create'])->name('admin.blogs.create');
        Route::post('blogs', [BlogController::class, 'store'])->name('admin.blogs.store');
        Route::get('blogs/{blog}/edit', [BlogController::class, 'edit'])->name('admin.blogs.edit');
        Route::post('blogs/{blog}', [BlogController::class, 'update'])->name('admin.blogs.update');
        Route::delete('blogs/{blog}', [BlogController::class, 'destroy'])->name('admin.blogs.destroy');
    });

// STAFF ATTENDANCE
Route::middleware(['auth', 'role:staff'])
    ->prefix('staff')
    ->group(function () {
        Route::get('/attendances', [AttendanceController::class, 'staffIndex'])
            ->name('staff.staff_attendances');
        Route::post('/attendances/{attendance}/check-in', [AttendanceController::class, 'checkIn'])
            ->name('staff.attendances.check_in');
        Route::post('/attendances/{attendance}/check-out', [AttendanceController::class, 'checkOut'])
            ->name('staff.attendances.check_out');
        Route::post('/attendances/{attendance}/late-reason', [AttendanceController::class, 'submitLateReason'])
            ->name('staff.attendances.submitLateReason');
        Route::post('/attendances/{attendance}/early-reason', [AttendanceController::class, 'submitEarlyReason'])
            ->name('staff.attendances.submitEarlyReason');
    });
