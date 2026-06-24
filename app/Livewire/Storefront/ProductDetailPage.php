<?php

namespace App\Livewire\Storefront;

use App\Models\Product;
use App\Services\Catalog\ProductConfigurationValidator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Detail Produk')]
class ProductDetailPage extends Component
{
    public Product $product;

    public ?int $variantId = null;

    // Array of option values: [group_id => [value_id]]
    public array $selectedOptions = [];

    // Array of addon IDs: [addon_id]
    public array $selectedAddons = [];

    public int $quantity = 1;

    public string $activeImagePath = '';

    public int $unitPrice = 0;

    public int $totalPrice = 0;

    public function mount(Product $product): void
    {
        $this->product = $product->load(['category', 'variants', 'images', 'optionGroups.values', 'addons']);

        // Default to first variant
        $firstVariant = $this->product->variants->where('is_active', true)->first();
        if ($firstVariant) {
            $this->variantId = $firstVariant->id;
            $this->unitPrice = $firstVariant->price;
            $this->totalPrice = $firstVariant->price;
        }

        // Set default primary image
        $primaryImage = $this->product->images->where('is_primary', true)->first() ?: $this->product->images->first();
        if ($primaryImage) {
            $this->activeImagePath = $primaryImage->path;
        }

        // Initialize selected options arrays for each group
        foreach ($this->product->optionGroups as $group) {
            $this->selectedOptions[$group->id] = [];
        }

        $this->updatePricing();
    }

    public function selectImage(string $path): void
    {
        $this->activeImagePath = $path;
    }

    public function incrementQuantity(): void
    {
        $this->quantity++;
        $this->updatePricing();
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
            $this->updatePricing();
        }
    }

    public function updatedVariantId(): void
    {
        $this->updatePricing();
    }

    public function updatedSelectedOptions(): void
    {
        $this->updatePricing();
    }

    public function updatedSelectedAddons(): void
    {
        $this->updatePricing();
    }

    public function updatePricing(): void
    {
        $validator = new ProductConfigurationValidator;

        try {
            $validated = $validator->validate($this->product, [
                'variant_id' => $this->variantId,
                'options' => $this->selectedOptions,
                'addons' => $this->selectedAddons,
                'quantity' => $this->quantity,
            ]);

            $this->unitPrice = $validated->unitPrice;
            $this->totalPrice = $validated->totalPrice;
        } catch (\InvalidArgumentException $e) {
            // Incomplete config, default to selected variant base price
            $variant = $this->product->variants->where('id', $this->variantId)->first();
            $basePrice = $variant ? $variant->price : 0;
            $this->unitPrice = $basePrice;
            $this->totalPrice = $basePrice * $this->quantity;
        }
    }

    public function submitConfiguration(): void
    {
        $validator = new ProductConfigurationValidator;

        try {
            $validated = $validator->validate($this->product, [
                'variant_id' => $this->variantId,
                'options' => $this->selectedOptions,
                'addons' => $this->selectedAddons,
                'quantity' => $this->quantity,
            ]);

            $this->dispatch('toast', message: 'Item berhasil ditambahkan ke keranjang (Demo). Total: Rp'.number_format($validated->totalPrice, 0, ',', '.'), variant: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), variant: 'error');
        }
    }

    public function render()
    {
        return view('livewire.storefront.product-detail-page');
    }
}
