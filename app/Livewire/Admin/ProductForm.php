<?php

namespace App\Livewire\Admin;

use App\Enums\SaleMode;
use App\Models\Addon;
use App\Models\Category;
use App\Models\OptionGroup;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Formulir Produk')]
class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public ?int $categoryId = null;

    public string $name = '';

    public string $description = '';

    public string $saleMode = 'ready_stock';

    public bool $isActive = true;

    public bool $isFeatured = false;

    // Array of variants: [['id' => null, 'name' => '', 'sku' => '', 'price' => 0, 'stock_on_hand' => 0]]
    public array $variants = [];

    // Checkbox selections for options and addons
    public array $selectedOptionGroups = [];

    public array $selectedAddons = [];

    // Temporary upload storage
    public $newImages = [];

    // Existing images (only for edit mode)
    public array $existingImages = [];

    protected array $rules = [
        'categoryId' => ['required', 'exists:categories,id'],
        'name' => ['required', 'string', 'max:150'],
        'description' => ['nullable', 'string'],
        'saleMode' => ['required', 'string', 'in:ready_stock,preorder,both'],
        'isActive' => ['boolean'],
        'isFeatured' => ['boolean'],
        'variants' => ['required', 'array', 'min:1'],
        'variants.*.name' => ['required', 'string', 'max:100'],
        'variants.*.sku' => ['required', 'string', 'max:100'],
        'variants.*.price' => ['required', 'integer', 'min:0'],
        'variants.*.stock_on_hand' => ['required', 'integer', 'min:0'],
        'newImages.*' => ['nullable', 'image', 'max:2048'], // Max 2MB per image
    ];

    protected array $messages = [
        'variants.required' => 'Produk harus memiliki minimal satu varian.',
        'variants.min' => 'Produk harus memiliki minimal satu varian.',
        'variants.*.name.required' => 'Nama varian wajib diisi.',
        'variants.*.sku.required' => 'SKU wajib diisi.',
        'variants.*.price.required' => 'Harga wajib diisi.',
        'variants.*.price.integer' => 'Harga harus berupa angka.',
        'variants.*.price.min' => 'Harga tidak boleh kurang dari 0.',
        'variants.*.stock_on_hand.required' => 'Stok wajib diisi.',
        'variants.*.stock_on_hand.integer' => 'Stok harus berupa angka.',
        'variants.*.stock_on_hand.min' => 'Stok tidak boleh kurang dari 0.',
    ];

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->product = $product;
            $this->categoryId = $product->category_id;
            $this->name = $product->name;
            $this->description = $product->description ?? '';
            $this->saleMode = $product->sale_mode->value;
            $this->isActive = (bool) $product->is_active;
            $this->isFeatured = (bool) $product->is_featured;

            // Load variants
            $this->variants = $product->variants->map(fn ($var) => [
                'id' => $var->id,
                'name' => $var->name,
                'sku' => $var->sku,
                'price' => $var->price,
                'stock_on_hand' => $var->stock_on_hand,
            ])->toArray();

            // Load options & addons
            $this->selectedOptionGroups = $product->optionGroups->pluck('id')->toArray();
            $this->selectedAddons = $product->addons->pluck('id')->toArray();

            // Existing images
            $this->loadExistingImages();
        } else {
            // Default 1 empty variant row
            $this->addVariantRow();
        }
    }

    public function loadExistingImages(): void
    {
        if ($this->product) {
            $this->existingImages = $this->product->images()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($img) => [
                    'id' => $img->id,
                    'path' => $img->path,
                    'is_primary' => (bool) $img->is_primary,
                ])
                ->toArray();
        }
    }

    public function addVariantRow(): void
    {
        $this->variants[] = [
            'id' => null,
            'name' => '',
            'sku' => '',
            'price' => 0,
            'stock_on_hand' => 0,
        ];
    }

    public function removeVariantRow(int $index): void
    {
        if (count($this->variants) > 1) {
            unset($this->variants[$index]);
            $this->variants = array_values($this->variants);
        } else {
            $this->dispatch('toast', message: 'Harus ada minimal satu varian.', variant: 'error');
        }
    }

    public function deleteExistingImage(int $imageId): void
    {
        $image = ProductImage::findOrFail($imageId);
        Storage::disk('public')->delete($image->path);
        $image->delete();

        $this->loadExistingImages();
        $this->dispatch('toast', message: 'Foto produk berhasil dihapus.', variant: 'success');
    }

    public function setPrimaryImage(int $imageId): void
    {
        if ($this->product) {
            $this->product->images()->update(['is_primary' => false]);
            $this->product->images()->where('id', $imageId)->update(['is_primary' => true]);

            $this->loadExistingImages();
            $this->dispatch('toast', message: 'Foto utama berhasil diperbarui.', variant: 'success');
        }
    }

    public function save()
    {
        $this->validate();

        // 1. Validate SKUs uniqueness internally & in database
        $skus = array_map('strtolower', array_column($this->variants, 'sku'));
        if (count($skus) !== count(array_unique($skus))) {
            $this->addError('variants', 'Terdapat SKU duplikat di dalam daftar varian.');

            return;
        }

        foreach ($this->variants as $index => $var) {
            $sku = $var['sku'];
            $varId = $var['id'] ?? null;
            $exists = ProductVariant::query()
                ->where('sku', $sku)
                ->when($varId, fn ($q) => $q->where('id', '!=', $varId))
                ->exists();
            if ($exists) {
                $this->addError("variants.{$index}.sku", "SKU '{$sku}' sudah digunakan oleh produk lain.");

                return;
            }
        }

        // 2. Generate unique slug
        $slug = Str::slug($this->name);
        $slugExists = Product::query()
            ->where('slug', $slug)
            ->when($this->product, fn ($q) => $q->where('id', '!=', $this->product->id))
            ->exists();

        if ($slugExists) {
            $slug = $slug.'-'.Str::random(5);
        }

        DB::transaction(function () use ($slug) {
            // 3. Save Product
            $productData = [
                'category_id' => $this->categoryId,
                'name' => $this->name,
                'slug' => $slug,
                'description' => $this->description,
                'sale_mode' => SaleMode::from($this->saleMode),
                'is_active' => $this->isActive,
                'is_featured' => $this->isFeatured,
            ];

            if ($this->product) {
                $this->product->update($productData);
                $product = $this->product;
            } else {
                $product = Product::create($productData);
            }

            // 4. Save Variants & Delete Removed Variants
            $keptVariantIds = [];
            foreach ($this->variants as $index => $var) {
                $variantData = [
                    'name' => $var['name'],
                    'sku' => $var['sku'],
                    'price' => $var['price'],
                    'stock_on_hand' => $var['stock_on_hand'],
                    'sort_order' => $index,
                    'is_active' => true,
                ];

                if (! empty($var['id'])) {
                    $variant = ProductVariant::findOrFail($var['id']);
                    $variant->update($variantData);
                    $keptVariantIds[] = $variant->id;
                } else {
                    $newVar = $product->variants()->create($variantData);
                    $keptVariantIds[] = $newVar->id;
                }
            }

            // Delete variants that were removed from the UI
            $product->variants()->whereNotIn('id', $keptVariantIds)->delete();

            // 5. Sync Option Groups & Addons
            $product->optionGroups()->sync($this->selectedOptionGroups);
            $product->addons()->sync($this->selectedAddons);

            // 6. Handle Image Uploads
            if (! empty($this->newImages)) {
                // Ensure storage directory exists
                if (! Storage::disk('public')->exists('products')) {
                    Storage::disk('public')->makeDirectory('products');
                }

                foreach ($this->newImages as $index => $image) {
                    $path = $image->store('products', 'public');

                    // First image or if there are no existing primary images, make it primary
                    $hasPrimary = $product->images()->where('is_primary', true)->exists();
                    $isPrimary = ! $hasPrimary && $index === 0;

                    $product->images()->create([
                        'path' => $path,
                        'alt_text' => $product->name,
                        'sort_order' => $index + count($this->existingImages),
                        'is_primary' => $isPrimary,
                    ]);
                }
            }
        });

        $message = $this->product ? 'Produk berhasil diperbarui.' : 'Produk berhasil dibuat.';
        $this->dispatch('toast', message: $message, variant: 'success');

        return redirect()->route('admin.products');
    }

    public function render()
    {
        $categories = Category::orderBy('sort_order')->get();
        $optionGroups = OptionGroup::all();
        $addons = Addon::all();

        return view('livewire.admin.product-form', [
            'categories' => $categories,
            'optionGroups' => $optionGroups,
            'addons' => $addons,
        ]);
    }
}
