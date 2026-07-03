<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products');
Route::resource('accounts', AccountController::class);
Route::resource('products', ProductController::class);
