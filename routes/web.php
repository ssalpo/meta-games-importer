<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\GenerationPromptController;
use App\Http\Controllers\MetaGameController;
use App\Http\Controllers\ProductEnglishGenerationController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products');
Route::resource('accounts', AccountController::class);
Route::resource('generation-prompts', GenerationPromptController::class)->except(['show']);
Route::get('meta-games', [MetaGameController::class, 'index'])->name('meta-games.index');
Route::post('meta-games/{metaGame}/products', [MetaGameController::class, 'createProduct'])->name('meta-games.create-product');
Route::post('products/generate-english-copy', ProductEnglishGenerationController::class)->name('products.generate-english-copy');
Route::resource('products', ProductController::class);
