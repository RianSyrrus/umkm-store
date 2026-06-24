<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kelola Kategori')]
class CategoryIndex extends Component
{
    use WithPagination;

    public string $name = '';

    public string $description = '';

    public bool $is_active = true;

    public int $sort_order = 0;

    public ?int $editingCategoryId = null;

    public string $search = '';

    protected array $rules = [
        'name' => ['required', 'string', 'max:100'],
        'description' => ['nullable', 'string', 'max:1000'],
        'is_active' => ['boolean'],
        'sort_order' => ['required', 'integer', 'min:0'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $this->validate();

        $slug = Str::slug($this->name);

        // Check unique slug (excluding current editing category)
        $slugExists = Category::query()
            ->where('slug', $slug)
            ->when($this->editingCategoryId, fn ($q) => $q->where('id', '!=', $this->editingCategoryId))
            ->exists();

        if ($slugExists) {
            $this->addError('name', 'Kategori dengan nama ini sudah ada (slug duplikat).');

            return;
        }

        if ($this->editingCategoryId) {
            $category = Category::findOrFail($this->editingCategoryId);
            $category->update([
                'name' => $this->name,
                'slug' => $slug,
                'description' => $this->description,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order,
            ]);

            $this->dispatch('toast', message: 'Kategori berhasil diperbarui.', variant: 'success');
        } else {
            Category::create([
                'name' => $this->name,
                'slug' => $slug,
                'description' => $this->description,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order,
            ]);

            $this->dispatch('toast', message: 'Kategori berhasil ditambahkan.', variant: 'success');
        }

        $this->resetInputFields();
    }

    public function editCategory(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->is_active = (bool) $category->is_active;
        $this->sort_order = (int) $category->sort_order;
    }

    public function deleteCategory(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->delete();

        $this->dispatch('toast', message: 'Kategori berhasil dihapus.', variant: 'success');
        $this->resetInputFields();
    }

    public function resetInputFields(): void
    {
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
        $this->sort_order = 0;
        $this->editingCategoryId = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $categories = Category::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.category-index', [
            'categories' => $categories,
        ]);
    }
}
