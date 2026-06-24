<?php

namespace App\Livewire\Admin;

use App\Actions\Inventory\AdjustStock;
use App\Models\ProductVariant;
use App\Models\Store;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Stok')]
class InventoryPage extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $onlyLowStock = false;

    // Modal state
    public bool $showAdjustmentModal = false;

    public ?int $selectedVariantId = null;

    public int $adjustQuantity = 0;

    public string $adjustType = 'adjustment';

    public string $adjustReason = '';

    public ?ProductVariant $selectedVariant = null;

    protected array $rules = [
        'adjustQuantity' => ['required', 'integer', 'not_in:0'],
        'adjustType' => ['required', 'string', 'in:restock,adjustment,waste'],
        'adjustReason' => ['nullable', 'string', 'max:255'],
    ];

    protected array $messages = [
        'adjustQuantity.not_in' => 'Jumlah penyesuaian tidak boleh 0.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedOnlyLowStock(): void
    {
        $this->resetPage();
    }

    public function openAdjustment(int $variantId): void
    {
        $this->selectedVariantId = $variantId;
        $this->selectedVariant = ProductVariant::with('product')->findOrFail($variantId);
        $this->adjustQuantity = 0;
        $this->adjustType = 'adjustment';
        $this->adjustReason = '';
        $this->showAdjustmentModal = true;
    }

    public function closeAdjustment(): void
    {
        $this->showAdjustmentModal = false;
        $this->selectedVariantId = null;
        $this->selectedVariant = null;
        $this->resetErrorBag();
    }

    public function submitAdjustment(AdjustStock $adjustStockAction): void
    {
        $this->validate();

        if (! $this->selectedVariant) {
            return;
        }

        try {
            $adjustStockAction->execute(
                variant: $this->selectedVariant,
                quantity: $this->adjustQuantity,
                type: $this->adjustType,
                reason: $this->adjustReason,
                actor: auth()->user()
            );

            $this->dispatch('toast', message: 'Stok berhasil disesuaikan.', variant: 'success');
            $this->closeAdjustment();
        } catch (\InvalidArgumentException $e) {
            $this->addError('adjustQuantity', $e->getMessage());
        }
    }

    public function render()
    {
        $store = Store::query()->first();
        $threshold = $store ? $store->low_stock_threshold : 5;

        $variants = ProductVariant::query()
            ->with(['product'])
            ->whereHas('product')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('sku', 'like', '%'.$this->search.'%')
                    ->orWhereHas('product', function ($pq) {
                        $pq->where('name', 'like', '%'.$this->search.'%');
                    });
            })
            ->when($this->onlyLowStock, function ($q) use ($threshold) {
                $q->whereRaw('(stock_on_hand - reserved_stock) <= ?', [$threshold]);
            })
            ->orderBy('sku')
            ->paginate(15);

        return view('livewire.admin.inventory-page', [
            'variants' => $variants,
            'threshold' => $threshold,
        ]);
    }
}
