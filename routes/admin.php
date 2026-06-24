<?php

use App\Livewire\Admin\StoreSettingsPage;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::view('/', 'admin.dashboard')->name('dashboard');
    Route::get('/settings', StoreSettingsPage::class)->name('settings');
});
