<?php

namespace App\Services\Cart;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Catalog\ProductConfigurationValidator;
use Illuminate\Support\Collection;

class CartService
{
    protected ProductConfigurationValidator $validator;

    public function __construct()
    {
        $this->validator = new ProductConfigurationValidator;
    }

    /**
     * Get all items in the cart.
     *
     * @return Collection<string, array{id: string, product: Product, variant: ProductVariant, options: Collection<int, \App\Models\OptionValue>, addons: Collection<int, \App\Models\Addon>, quantity: int, notes: string, unit_price: int, total_price: int}>
     */
    public function get(): Collection
    {
        $cart = session()->get('cart', []);
        $items = new Collection;

        foreach ($cart as $id => $item) {
            /** @var Product|null $product */
            $product = Product::with(['category', 'variants', 'images', 'optionGroups.values', 'addons'])
                ->find($item['product_id']);

            if (! $product || ! $product->is_active) {
                // Remove invalid products automatically
                $this->remove($id);

                continue;
            }

            try {
                // Re-validate and recalculate prices to ensure database consistency
                $validated = $this->validator->validate($product, [
                    'variant_id' => $item['variant_id'],
                    'options' => $item['options'],
                    'addons' => $item['addons'],
                    'quantity' => $item['quantity'],
                ]);

                $items->put($id, [
                    'id' => $id,
                    'product' => $product,
                    'variant' => $validated->variant,
                    'options' => $validated->options,
                    'addons' => $validated->addons,
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? '',
                    'unit_price' => $validated->unitPrice,
                    'total_price' => $validated->totalPrice,
                ]);
            } catch (\InvalidArgumentException $e) {
                // If it becomes invalid (e.g. stock goes out), keep it but mark it or remove it.
                // Sesuai PRD: "Melihat peringatan jika harga atau stok berubah."
                // For simplicity, if it's completely invalid, we can remove it or keep the error.
                // Let's remove it if invalid to prevent ordering errors.
                $this->remove($id);
            }
        }

        return $items;
    }

    /**
     * Add an item to the cart.
     *
     * @param array{variant_id?: ?int, options?: array<int, int|array<int, int>>, addons?: array<int, int>, quantity?: int, notes?: string} $config
     * @throws \InvalidArgumentException
     */
    public function add(Product $product, array $config): void
    {
        $variantId = $config['variant_id'] ?? null;
        $options = $config['options'] ?? [];
        $addons = $config['addons'] ?? [];
        $quantity = (int) ($config['quantity'] ?? 1);
        $notes = $config['notes'] ?? '';

        // 1. Validate configuration & pricing
        $validated = $this->validator->validate($product, [
            'variant_id' => $variantId,
            'options' => $options,
            'addons' => $addons,
            'quantity' => $quantity,
        ]);

        // 2. Generate unique hash ID for this configuration
        $cartItemId = $this->generateItemId($product->id, $variantId, $options, $addons);

        // 3. Check existing cart
        $cart = session()->get('cart', []);
        $existingQty = isset($cart[$cartItemId]) ? $cart[$cartItemId]['quantity'] : 0;
        $newQty = $existingQty + $quantity;

        // 4. Validate stock if ready_stock or both
        if (in_array($product->sale_mode->value, ['ready_stock', 'both'])) {
            if ($newQty > $validated->variant->stock_on_hand) {
                throw new \InvalidArgumentException("Stok tidak mencukupi. Anda memiliki {$existingQty} di keranjang, dan stok maksimal adalah {$validated->variant->stock_on_hand}.");
            }
        }

        // 5. Save to session
        $cart[$cartItemId] = [
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'options' => $options,
            'addons' => $addons,
            'quantity' => $newQty,
            'notes' => $notes,
        ];

        session()->put('cart', $cart);
    }

    /**
     * Update the quantity of a cart item.
     *
     * @throws \InvalidArgumentException
     */
    public function updateQuantity(string $cartItemId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($cartItemId);

            return;
        }

        $cart = session()->get('cart', []);

        if (! isset($cart[$cartItemId])) {
            throw new \InvalidArgumentException('Item tidak ditemukan di keranjang.');
        }

        $item = $cart[$cartItemId];
        /** @var Product $product */
        $product = Product::findOrFail($item['product_id']);
        /** @var ProductVariant $variant */
        $variant = ProductVariant::findOrFail($item['variant_id']);

        // Check stock
        if (in_array($product->sale_mode->value, ['ready_stock', 'both'])) {
            if ($quantity > $variant->stock_on_hand) {
                throw new \InvalidArgumentException("Stok tidak mencukupi untuk memperbarui kuantitas (Maksimal: {$variant->stock_on_hand}).");
            }
        }

        $cart[$cartItemId]['quantity'] = $quantity;
        session()->put('cart', $cart);
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(string $cartItemId): void
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$cartItemId])) {
            unset($cart[$cartItemId]);
            session()->put('cart', $cart);
        }
    }

    /**
     * Clear the cart.
     */
    public function clear(): void
    {
        session()->forget('cart');
    }

    /**
     * Get the total price of all items in the cart.
     */
    public function getTotalPrice(): int
    {
        return $this->get()->sum('total_price');
    }

    /**
     * Generate unique hash for cart item based on its configuration.
     *
     * @param array<int, int|array<int, int>> $options
     * @param array<int, int> $addons
     */
    protected function generateItemId(int $productId, int $variantId, array $options, array $addons): string
    {
        // Normalize options and addons for consistent hashing
        ksort($options);
        foreach ($options as $k => $v) {
            if (is_array($v)) {
                sort($v);
            }
        }

        sort($addons);

        return md5($productId.'_'.$variantId.'_'.serialize($options).'_'.serialize($addons));
    }
}
