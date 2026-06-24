<?php

use App\Livewire\Storefront\CartPage;
use App\Livewire\Storefront\CatalogPage;
use App\Livewire\Storefront\CheckoutPage;
use App\Livewire\Storefront\ProductDetailPage;

Route::get('/', CatalogPage::class)->name('home');
Route::get('/products/{product:slug}', ProductDetailPage::class)->name('products.show');
Route::get('/cart', CartPage::class)->name('home.cart');
Route::get('/checkout', CheckoutPage::class)->name('home.checkout');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
