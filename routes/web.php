<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Routing\Router;

// Trang chủ khách hàng
Route::get('/', HomeController::class . '@index')->name('pages.home');  

Route::get('/trangchu', HomeController::class . '@index')->name('pages.trangchu');



// Trang quản trị
// Trang đăng nhập, đăng xuất admin
Route::get('/admin/login', 'App\Http\Controllers\AdminController@login')->name('admin.login');
Route::post('/admin/login', 'App\Http\Controllers\AdminController@authenticate')->name('admin.authenticate');
Route::get('/admin/register', 'App\Http\Controllers\AdminController@register')->name('admin.register');
Route::get('/admin/dashboard', 'App\Http\Controllers\AdminController@index')->name('admin.dashboard');
Route::get('/admin/logout', 'App\Http\Controllers\AdminController@logout')->name('admin.logout');

// Quản lý danh mục sản phẩm
Route::get('/admin/categories', 'App\Http\Controllers\CategoryProductController@list')->name('admin.categories.list');
Route::get('/admin/categories/create', 'App\Http\Controllers\CategoryProductController@create')->name('admin.categories.create');
Route::get('/admin/categories/edit', 'App\Http\Controllers\CategoryProductController@edit')->name('admin.categories.edit');
