<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kelola Produk')]
class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $categoryId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function deleteProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->delete();

        $this->dispatch('toast', message: 'Produk berhasil dihapus.', variant: 'success');
    }

    public function render()
    {
        $categories = Category::orderBy('sort_order')->get();

        $products = Product::query()
            ->with(['category', 'variants'])
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->when($this->categoryId, function ($q) {
                $q->where('category_id', $this->categoryId);
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.product-index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
