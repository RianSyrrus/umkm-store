<?php

namespace App\Livewire\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.storefront')]
#[Title('Katalog Toko')]
class CatalogPage extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $selectedCategoryId = null;

    public string $saleModeFilter = ''; // ready_stock, preorder, or empty for all

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
        $this->resetPage();
    }

    public function filterSaleMode(string $mode): void
    {
        $this->saleModeFilter = $mode;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $store = Store::query()->with('businessHours')->first();

        $isOpen = false;
        $todayHour = null;
        if ($store) {
            $now = now();
            $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
            $todayHour = $store->businessHours->where('day_of_week', $dayOfWeek)->first();

            if ($todayHour && ! $todayHour->is_closed && $todayHour->open_time && $todayHour->close_time) {
                $currentTime = $now->format('H:i:s');
                $isOpen = $currentTime >= $todayHour->open_time && $currentTime <= $todayHour->close_time;
            }
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $products = Product::query()
            ->with(['category', 'variants', 'images'])
            ->where('is_active', true)
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->when($this->selectedCategoryId, function ($q) {
                $q->where('category_id', $this->selectedCategoryId);
            })
            ->when($this->saleModeFilter, function ($q) {
                $q->where('sale_mode', $this->saleModeFilter);
            })
            ->orderBy('is_featured', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(12);

        return view('livewire.storefront.catalog-page', [
            'store' => $store,
            'isOpen' => $isOpen,
            'todayHour' => $todayHour,
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}
