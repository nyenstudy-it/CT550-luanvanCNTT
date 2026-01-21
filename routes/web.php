<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CategoryProductController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;
use Termwind\Components\Raw;

// Trang chủ khách hàng
Route::get('/', [HomeController::class, 'index'])->name('pages.home');
Route::get('/trangchu', [HomeController::class, 'index'])->name('pages.trangchu');
Route::get('/categories/{id}', [HomeController::class, 'showCategory'])->name('categories.show');


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
    });

// Staff checkin và checkout
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


//ADMIN + STAFF 
Route::middleware(['auth', 'role:admin,staff'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/dashboard', [AdminController::class, 'index'])
            ->name('admin.dashboard');

    // Trang quản lý hồ sơ cá nhân
        Route::get('/profile', [AdminController::class, 'profile'])
            ->name('profile.show');

        Route::post('/profile/update', [AdminController::class, 'profileUpdate'])
            ->name('profile.update');

        // Quản lý nhà cung cấp
        Route::get('/suppliers', [SupplierController::class, 'index'])
            ->name('admin.suppliers.list');
        Route::get('/suppliers/create', [SupplierController::class, 'create'])
            ->name('admin.suppliers.create');
        Route::post('/suppliers/store', [SupplierController::class, 'store'])
            ->name('admin.suppliers.store');
        Route::get('/suppliers/edit/{id}', [SupplierController::class, 'edit'])
            ->name('admin.suppliers.edit');
        Route::post('/suppliers/update/{id}', [SupplierController::class, 'update'])
            ->name('admin.suppliers.update');
        Route::delete('/suppliers/destroy/{id}', [SupplierController::class, 'destroy'])
            ->name('admin.suppliers.destroy');

        // Quản lý danh mục
        Route::get('/categories', [CategoryProductController::class, 'list'])
            ->name('admin.categories.list');
        Route::get('/categories/create', [CategoryProductController::class, 'create'])
            ->name('admin.categories.create');
        Route::get('/categories/edit/{id}', [CategoryProductController::class, 'edit'])
            ->name('admin.categories.edit');
        Route::post('/categories/store', [CategoryProductController::class, 'store'])
            ->name('admin.categories.store');
        Route::post('/categories/update/{id}', [CategoryProductController::class, 'update'])
            ->name('admin.categories.update');
        Route::delete('/categories/destroy/{id}', [CategoryProductController::class, 'destroy'])
            ->name('admin.categories.destroy');
    
    });

