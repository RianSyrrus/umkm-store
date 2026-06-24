<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('redirects guests away from admin dashboard', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('allows the seeded admin to login and logout', function () {
    User::factory()->create([
        'email' => 'admin@umkm.test',
        'password' => Hash::make('password'),
    ]);

    $this->post('/admin/login', [
        'email' => 'admin@umkm.test',
        'password' => 'password',
    ])->assertRedirect('/admin');

    $this->assertAuthenticated();

    $this->post('/admin/logout')->assertRedirect('/admin/login');

    $this->assertGuest();
});
