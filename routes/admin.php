<?php

use App\Livewire\Admin\CategoryIndex;
use App\Livewire\Admin\InventoryPage;
use App\Livewire\Admin\ProductForm;
use App\Livewire\Admin\ProductIndex;
use App\Livewire\Admin\ScheduleSlotIndex;
use App\Livewire\Admin\StoreSettingsPage;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::view('/', 'admin.dashboard')->name('dashboard');
    Route::get('/settings', StoreSettingsPage::class)->name('settings');
    Route::get('/categories', CategoryIndex::class)->name('categories');
    Route::get('/products', ProductIndex::class)->name('products');
    Route::get('/products/create', ProductForm::class)->name('products.create');
    Route::get('/products/{product}/edit', ProductForm::class)->name('products.edit');
    Route::get('/inventory', InventoryPage::class)->name('inventory');
    Route::get('/schedule-slots', ScheduleSlotIndex::class)->name('schedule-slots');
});
