<?php

test('serves the storefront home page', function () {
    $this->get('/')->assertOk()->assertSee('UMKM Store');
});
