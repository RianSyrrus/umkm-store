<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <flux:heading size="xl">Kelola Produk</flux:heading>
            <flux:text>Kelola katalog produk, varian harga, opsi variasi, dan ketersediaan stok.</flux:text>
        </div>
        <flux:button 
            as="a" 
            :href="route('admin.products.create')" 
            icon="plus" 
            variant="primary"
            wire:navigate
        >
            Tambah Produk
        </flux:button>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 space-y-4">
        <!-- Filter dan Pencarian -->
        <div class="flex flex-col md:flex-row items-stretch md:items-center gap-4">
            <div class="flex-1 max-w-sm">
                <flux:input 
                    wire:model.live="search" 
                    placeholder="Cari nama atau deskripsi produk..." 
                    icon="magnifying-glass" 
                    clearable 
                />
            </div>
            <div class="w-full md:w-64">
                <flux:select wire:model.live="categoryId" placeholder="Semua Kategori">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <!-- Tabel Produk -->
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nama Produk</flux:table.column>
                <flux:table.column>Kategori</flux:table.column>
                <flux:table.column>Mode Jual</flux:table.column>
                <flux:table.column>Harga Varian</flux:table.column>
                <flux:table.column>Stok</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column class="text-right">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($products as $product)
                    <flux:table.row :key="$product->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                @if($product->images->where('is_primary', true)->first())
                                    <img 
                                        src="{{ asset('storage/' . $product->images->where('is_primary', true)->first()->path) }}" 
                                        alt="{{ $product->name }}" 
                                        class="h-10 w-10 rounded-lg object-cover border border-zinc-150 dark:border-zinc-700"
                                    />
                                @elseif($product->images->first())
                                    <img 
                                        src="{{ asset('storage/' . $product->images->first()->path) }}" 
                                        alt="{{ $product->name }}" 
                                        class="h-10 w-10 rounded-lg object-cover border border-zinc-150 dark:border-zinc-700"
                                    />
                                @else
                                    <div class="h-10 w-10 rounded-lg bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center border border-zinc-150 dark:border-zinc-700">
                                        <flux:icon icon="photo" class="h-5 w-5 text-zinc-400" />
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                                        {{ $product->name }}
                                        @if($product->is_featured)
                                            <flux:badge color="amber" size="sm">Rekomendasi</flux:badge>
                                        @endif
                                    </div>
                                    <div class="text-xs text-zinc-500 font-mono mt-0.5">
                                        {{ $product->variants->count() }} Varian
                                    </div>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $product->category->name }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($product->sale_mode->value === 'ready_stock')
                                <flux:badge color="green" size="sm">Ready Stock</flux:badge>
                            @elseif ($product->sale_mode->value === 'preorder')
                                <flux:badge color="indigo" size="sm">Pre-Order</flux:badge>
                            @else
                                <flux:badge color="cyan" size="sm">Ready & PO</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @php
                                $prices = $product->variants->pluck('price')->unique();
                            @endphp
                            @if ($prices->count() === 1)
                                <span class="font-medium text-zinc-900 dark:text-white">
                                    Rp{{ number_format($prices->first(), 0, ',', '.') }}
                                </span>
                            @elseif ($prices->count() > 1)
                                <span class="font-medium text-zinc-900 dark:text-white">
                                    Rp{{ number_format($prices->min(), 0, ',', '.') }} - Rp{{ number_format($prices->max(), 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-zinc-400">-</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @php
                                $totalStock = $product->variants->sum('stock_on_hand');
                            @endphp
                            <span class="font-mono text-sm {{ $totalStock <= 5 ? 'text-red-600 font-bold' : 'text-zinc-600 dark:text-zinc-400' }}">
                                {{ $totalStock }} unit
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($product->is_active)
                                <flux:badge color="green" size="sm">Aktif</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">Nonaktif</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button 
                                    as="a"
                                    :href="route('admin.products.edit', $product->id)" 
                                    icon="pencil" 
                                    variant="ghost" 
                                    size="sm" 
                                    title="Ubah Produk"
                                    wire:navigate
                                />
                                <flux:button 
                                    wire:click="deleteProduct({{ $product->id }})" 
                                    wire:confirm="Apakah Anda yakin ingin menghapus produk ini beserta seluruh variannya?"
                                    icon="trash" 
                                    variant="ghost" 
                                    size="sm" 
                                    color="red" 
                                    title="Hapus Produk" 
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8 text-zinc-400">
                            Tidak ada produk ditemukan.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
</div>
