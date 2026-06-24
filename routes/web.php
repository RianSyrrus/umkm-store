<?php

use App\Livewire\Storefront\CatalogPage;
use App\Livewire\Storefront\ProductDetailPage;

Route::get('/', CatalogPage::class)->name('home');
Route::get('/products/{product:slug}', ProductDetailPage::class)->name('products.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
