<?php

use App\Models\Store;

test('storefront pages contain semantic HTML and accessibility markup', function () {
    Store::factory()->create([
        'name' => 'Cemilan Enak',
        'address' => 'Bandung',
    ]);

    $response = $this->get(route('home'))->assertOk();

    // Verify HTML5 semantic elements
    $response->assertSee('<header', false);
    $response->assertSee('<main', false);
    $response->assertSee('<nav', false);
});
