<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/accounts');
Route::resource('accounts', AccountController::class);
