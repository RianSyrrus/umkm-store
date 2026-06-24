<?php

namespace App\Livewire\Storefront;

use App\Services\Cart\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Keranjang Belanja')]
class CartPage extends Component
{
    public int $total = 0;

    public function increment(string $itemId): void
    {
        $cartService = new CartService;
        $items = $cartService->get();

        if ($items->has($itemId)) {
            $item = $items->get($itemId);
            try {
                $cartService->updateQuantity($itemId, $item['quantity'] + 1);
            } catch (\InvalidArgumentException $e) {
                $this->dispatch('toast', message: $e->getMessage(), variant: 'error');
            }
        }
    }

    public function decrement(string $itemId): void
    {
        $cartService = new CartService;
        $items = $cartService->get();

        if ($items->has($itemId)) {
            $item = $items->get($itemId);
            if ($item['quantity'] > 1) {
                $cartService->updateQuantity($itemId, $item['quantity'] - 1);
            } else {
                $this->removeItem($itemId);
            }
        }
    }

    public function removeItem(string $itemId): void
    {
        $cartService = new CartService;
        $cartService->remove($itemId);
        $this->dispatch('toast', message: 'Item berhasil dihapus dari keranjang.', variant: 'success');
    }

    public function render()
    {
        $cartService = new CartService;
        $items = $cartService->get();
        $this->total = $cartService->getTotalPrice();

        return view('livewire.storefront.cart-page', [
            'items' => $items,
            'total' => $this->total,
        ]);
    }
}
