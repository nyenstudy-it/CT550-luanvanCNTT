<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CategoryProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SalaryController;
use Illuminate\Support\Facades\Route;
use Termwind\Components\Raw;

// Trang chủ khách hàng
Route::get('/', [HomeController::class, 'index'])->name('pages.home');
Route::get('/trangchu', [HomeController::class, 'index'])->name('pages.trangchu');
Route::get('/categories/{id}', [HomeController::class, 'showCategory'])->name('categories.show');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart', [CartController::class, 'list'])->name('cart.list');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');


Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/my-orders', [CheckoutController::class, 'myOrders'])
        ->name('orders.my');

    Route::get('/my-orders/{id}', [CheckoutController::class, 'orderDetail'])
        ->name('orders.detail');

    Route::post('/my-orders/{id}/cancel', [CheckoutController::class, 'cancel'])
        ->name('orders.cancel');
});



//Đăng ký ADMIN/STAFF
Route::middleware(['auth', 'role:admin'])
    ->get('/admin/register', [AdminController::class, 'register'])
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

        Route::get('/staff/attendances', [AttendanceController::class, 'index'])
            ->name('admin.staff.attendances');
        Route::get('/staff/attendances/create', [AttendanceController::class, 'create'])
            ->name('admin.staff.attendances.create');
        Route::post('/staff/attendances', [AttendanceController::class, 'store'])
            ->name('admin.staff.attendances.store');
    Route::get('/staff/attendances/{attendance}/edit',[AttendanceController::class, 'edit']
            )->name('admin.staff.attendances.edit');

    Route::post('/staff/attendances/{attendance}',[AttendanceController::class, 'update']
            )->name('admin.staff.attendances.update');
    Route::delete('/staff/attendances/{attendance}',[AttendanceController::class, 'destroy']
            )->name('admin.staff.attendances.destroy');

    Route::get('/staff/salaries', [SalaryController::class, 'index'])
            ->name('admin.staff.salaries');
        Route::post('/staff/salaries/calculate', [SalaryController::class, 'calculate'])
            ->name('admin.staff.salaries.calculate');
    });

// ADMIN + STAFF
Route::middleware(['auth', 'role:admin,staff'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

        Route::get('/profile', [AdminController::class, 'profile'])->name('profile.show');
        Route::post('/profile/update', [AdminController::class, 'profileUpdate'])->name('profile.update');

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
        Route::get(
        '/products/{id}/popup',
        [ProductController::class, 'showPopup']
    )->name('admin.products.popup');



    // Product Variants
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

        // IMPORT 
        Route::get('/imports', [ImportController::class, 'list'])->name('admin.imports.list');
        Route::get('/imports/create', [ImportController::class, 'create'])->name('admin.imports.create');
        Route::post('/imports/store', [ImportController::class, 'store'])->name('admin.imports.store'); 
        Route::get('/imports/{id}', [ImportController::class, 'show'])->name('admin.imports.show');
        Route::get('/imports/{id}/print', [ImportController::class, 'print'])->name('admin.imports.print');


        // INVENTORY
        Route::get('/inventories', [InventoryController::class, 'list'])->name('admin.inventories.list');

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
    });
