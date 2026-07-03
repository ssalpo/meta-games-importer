<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ExtensionProductController;
use App\Http\Controllers\GenerationPromptController;
use App\Http\Controllers\MetaGameController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductEnglishGenerationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products');
Route::resource('accounts', AccountController::class);
Route::get('extension/products', [ExtensionProductController::class, 'index'])->name('extension.products.index');
Route::get('extension/products/{product}', [ExtensionProductController::class, 'show'])->name('extension.products.show');
Route::patch('extension/products/{product}/ggsel-offer-id', [ExtensionProductController::class, 'updateGgselOfferId'])->name('extension.products.ggsel-offer-id');
Route::post('extension/products/{product}/publish', [ExtensionProductController::class, 'publish'])->name('extension.products.publish');
Route::resource('generation-prompts', GenerationPromptController::class)->except(['show']);
Route::get('meta-games', [MetaGameController::class, 'index'])->name('meta-games.index');
Route::post('meta-games/{metaGame}/products', [MetaGameController::class, 'createProduct'])->name('meta-games.create-product');
Route::post('products/generate-english-copy', ProductEnglishGenerationController::class)->name('products.generate-english-copy');
Route::resource('products', ProductController::class);
