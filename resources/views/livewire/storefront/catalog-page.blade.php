<div class="space-y-8">
    <!-- Banner Info Toko -->
    @if ($store)
        <div class="relative overflow-hidden rounded-2xl bg-white p-6 md:p-8 border border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-4 max-w-2xl">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl md:text-3xl font-extrabold text-zinc-950 dark:text-white">{{ $store->name }}</h1>
                    @if ($isOpen)
                        <flux:badge color="green" size="sm" class="font-bold">Buka</flux:badge>
                    @else
                        <flux:badge color="red" size="sm" class="font-bold">Tutup</flux:badge>
                    @endif
                </div>
                
                <p class="text-zinc-600 dark:text-zinc-400 text-sm md:text-base leading-relaxed">
                    {{ $store->description ?: 'Selamat datang di toko kami!' }}
                </p>

                <div class="flex flex-col sm:flex-row gap-x-6 gap-y-2 text-xs md:text-sm text-zinc-500 dark:text-zinc-400">
                    <div class="flex items-center gap-1.5">
                        <flux:icon icon="map-pin" class="h-4.5 w-4.5 text-zinc-400" />
                        <span>{{ $store->address }}</span>
                    </div>
                    @if ($todayHour && !$todayHour->is_closed)
                        <div class="flex items-center gap-1.5">
                            <flux:icon icon="clock" class="h-4.5 w-4.5 text-zinc-400" />
                            <span>Hari Ini: {{ substr($todayHour->open_time, 0, 5) }} - {{ substr($todayHour->close_time, 0, 5) }}</span>
                        </div>
                    @else
                        <div class="flex items-center gap-1.5">
                            <flux:icon icon="clock" class="h-4.5 w-4.5 text-zinc-400" />
                            <span>Tutup Hari Ini</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <flux:button 
                    as="a" 
                    href="https://wa.me/{{ $store->whatsapp }}" 
                    target="_blank"
                    icon="phone"
                    variant="primary"
                    class="font-bold"
                >
                    Hubungi WhatsApp
                </flux:button>
            </div>
        </div>
    @else
        <div class="rounded-2xl bg-zinc-100 p-8 text-center text-zinc-400 dark:bg-zinc-800">
            Toko belum dikonfigurasi. Silakan jalankan seeder atau konfigurasikan di halaman admin.
        </div>
    @endif

    <!-- Pencarian & Filter Kategori -->
    <div class="space-y-4">
        <div class="flex flex-col md:flex-row items-stretch md:items-center gap-4">
            <!-- Search -->
            <div class="flex-1">
                <flux:input 
                    wire:model.live="search" 
                    placeholder="Mau makan atau minum apa hari ini? Cari di sini..." 
                    icon="magnifying-glass" 
                    size="lg"
                    clearable 
                />
            </div>
            
            <!-- Sale Mode Filter -->
            <div class="flex items-center gap-2 bg-zinc-200/50 dark:bg-zinc-800/50 p-1 rounded-xl self-start md:self-auto">
                <flux:button 
                    wire:click="filterSaleMode('')" 
                    variant="{{ $saleModeFilter === '' ? 'filled' : 'ghost' }}"
                    size="sm"
                    class="font-semibold"
                >
                    Semua
                </flux:button>
                <flux:button 
                    wire:click="filterSaleMode('ready_stock')" 
                    variant="{{ $saleModeFilter === 'ready_stock' ? 'filled' : 'ghost' }}"
                    size="sm"
                    class="font-semibold"
                >
                    Ready Stock
                </flux:button>
                <flux:button 
                    wire:click="filterSaleMode('preorder')" 
                    variant="{{ $saleModeFilter === 'preorder' ? 'filled' : 'ghost' }}"
                    size="sm"
                    class="font-semibold"
                >
                    Pre-Order
                </flux:button>
            </div>
        </div>

        <!-- Category Pills (Horizontal scroll on mobile) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none -mx-4 px-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:px-0">
            <button 
                wire:click="selectCategory(null)" 
                class="shrink-0 px-4 py-2 text-sm font-semibold rounded-full border transition-all duration-200 {{ is_null($selectedCategoryId) ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-white dark:border-white dark:text-zinc-950 shadow-sm' : 'bg-white border-zinc-200 text-zinc-700 hover:border-zinc-300 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-600' }}"
            >
                Semua Kategori
            </button>
            @foreach ($categories as $cat)
                <button 
                    wire:click="selectCategory({{ $cat->id }})" 
                    class="shrink-0 px-4 py-2 text-sm font-semibold rounded-full border transition-all duration-200 {{ $selectedCategoryId === $cat->id ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-white dark:border-white dark:text-zinc-950 shadow-sm' : 'bg-white border-zinc-200 text-zinc-700 hover:border-zinc-300 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-600' }}"
                >
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse ($products as $product)
            @php
                $primaryImage = $product->images->where('is_primary', true)->first() ?: $product->images->first();
                $minPrice = $product->variants->min('price');
            @endphp
            <a 
                href="{{ route('products.show', $product->slug) }}" 
                class="group flex flex-col h-full bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden hover:shadow-md transition-all duration-300 relative"
                wire:navigate
            >
                <!-- Thumbnail Image -->
                <div class="aspect-square w-full bg-zinc-100 dark:bg-zinc-900 relative overflow-hidden">
                    @if ($primaryImage)
                        <img 
                            src="{{ asset('storage/' . $primaryImage->path) }}" 
                            alt="{{ $product->name }}" 
                            class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                    @else
                        <div class="h-full w-full flex items-center justify-center text-zinc-400">
                            <flux:icon icon="photo" class="h-10 w-10 text-zinc-300 dark:text-zinc-700" />
                        </div>
                    @endif

                    <!-- Featured/SaleMode Badges on Thumbnail -->
                    <div class="absolute top-3 left-3 flex flex-col gap-1.5 items-start">
                        @if ($product->is_featured)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold tracking-wider uppercase bg-amber-500 text-white shadow-sm">
                                <flux:icon icon="star" class="h-3 w-3" />
                                Rekomendasi
                            </span>
                        @endif

                        @if ($product->sale_mode->value === 'preorder')
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-[10px] font-bold tracking-wider uppercase bg-indigo-600 text-white shadow-sm">
                                Pre-Order
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Info Body -->
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div class="space-y-1.5">
                        <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block">
                            {{ $product->category->name }}
                        </span>
                        <h3 class="font-bold text-zinc-900 dark:text-white group-hover:text-zinc-950 dark:group-hover:text-white line-clamp-1">
                            {{ $product->name }}
                        </h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2 leading-relaxed">
                            {{ $product->description ?: 'Tidak ada deskripsi.' }}
                        </p>
                    </div>

                    <div class="pt-4 mt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-zinc-400 uppercase tracking-wider block font-medium">Mulai Dari</span>
                            <span class="font-mono text-base font-extrabold text-zinc-900 dark:text-white">
                                Rp{{ number_format($minPrice, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        <div class="h-9 w-9 rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 flex items-center justify-center group-hover:bg-zinc-900 group-hover:text-white dark:group-hover:bg-white dark:group-hover:text-zinc-900 transition-colors duration-200">
                            <flux:icon icon="chevron-right" class="h-5 w-5" />
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full py-12 text-center text-zinc-400">
                <flux:icon icon="squares-plus" class="h-12 w-12 mx-auto text-zinc-300 dark:text-zinc-700 mb-3" />
                <p class="font-medium">Tidak ada produk ditemukan.</p>
                <p class="text-sm mt-1">Coba ubah kata kunci pencarian Anda.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-6">
        {{ $products->links() }}
    </div>
</div>
