<div class="space-y-8">
    <!-- Banner Info Toko -->
    @if ($store)
        <div class="relative overflow-hidden rounded-3xl bg-white p-6 md:p-8 border border-stone-200 dark:border-stone-800 dark:bg-stone-950 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-4 max-w-2xl">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-3xl md:text-4xl font-black text-stone-950 dark:text-white tracking-tight">{{ $store->name }}</h1>
                    @if ($isOpen)
                        <flux:badge color="green" size="sm" class="font-bold">Buka</flux:badge>
                    @else
                        <flux:badge color="red" size="sm" class="font-bold">Tutup</flux:badge>
                    @endif
                </div>
                
                <p class="text-stone-600 dark:text-stone-400 text-sm md:text-base leading-relaxed">
                    {{ $store->description ?: 'Selamat datang di toko kami!' }}
                </p>

                <div class="flex flex-col sm:flex-row gap-x-6 gap-y-2 text-xs md:text-sm text-stone-500 dark:text-stone-400">
                    <div class="flex items-center gap-1.5">
                        <flux:icon icon="map-pin" class="h-4.5 w-4.5 text-stone-400 dark:text-stone-500" />
                        <span>{{ $store->address }}</span>
                    </div>
                    @if ($todayHour && !$todayHour->is_closed)
                        <div class="flex items-center gap-1.5">
                            <flux:icon icon="clock" class="h-4.5 w-4.5 text-stone-400 dark:text-stone-500" />
                            <span>Hari Ini: {{ substr($todayHour->open_time, 0, 5) }} - {{ substr($todayHour->close_time, 0, 5) }}</span>
                        </div>
                    @else
                        <div class="flex items-center gap-1.5">
                            <flux:icon icon="clock" class="h-4.5 w-4.5 text-stone-400 dark:text-stone-500" />
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
                    class="font-extrabold shadow-sm transition-transform duration-300 hover:scale-[1.02] active:scale-[0.98]"
                >
                    Hubungi WhatsApp
                </flux:button>
            </div>
        </div>
    @else
        <div class="rounded-3xl bg-stone-100 p-8 text-center text-stone-400 dark:bg-stone-900 border border-stone-200 dark:border-stone-800">
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
                    class="bg-white dark:bg-stone-950 border-stone-200 dark:border-stone-850"
                />
            </div>
            
            <!-- Sale Mode Filter -->
            <div class="flex items-center gap-1 bg-stone-200/60 dark:bg-stone-900/60 p-1.5 rounded-2xl self-start md:self-auto border border-stone-200/50 dark:border-stone-800/50">
                <flux:button 
                    wire:click="filterSaleMode('')" 
                    variant="{{ $saleModeFilter === '' ? 'filled' : 'ghost' }}"
                    size="sm"
                    class="font-bold rounded-xl"
                >
                    Semua
                </flux:button>
                <flux:button 
                    wire:click="filterSaleMode('ready_stock')" 
                    variant="{{ $saleModeFilter === 'ready_stock' ? 'filled' : 'ghost' }}"
                    size="sm"
                    class="font-bold rounded-xl"
                >
                    Ready Stock
                </flux:button>
                <flux:button 
                    wire:click="filterSaleMode('preorder')" 
                    variant="{{ $saleModeFilter === 'preorder' ? 'filled' : 'ghost' }}"
                    size="sm"
                    class="font-bold rounded-xl"
                >
                    Pre-Order
                </flux:button>
            </div>
        </div>

        <!-- Category Pills (Horizontal scroll on mobile, with minimum 44px tap targets) -->
        <div class="flex items-center gap-2.5 overflow-x-auto pb-3.5 scrollbar-none -mx-4 px-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:px-0">
            <button 
                wire:click="selectCategory(null)" 
                class="shrink-0 flex items-center justify-center min-h-[44px] px-5 py-2 text-sm font-bold rounded-full border transition-all duration-350 ease-out-quint hover:scale-[1.02] active:scale-[0.98] {{ is_null($selectedCategoryId) ? 'bg-stone-950 border-stone-950 text-white dark:bg-white dark:border-white dark:text-stone-950 shadow-sm' : 'bg-white border-stone-200 text-stone-700 hover:border-stone-300 dark:bg-stone-900 dark:border-stone-800 dark:text-stone-350 dark:hover:border-stone-700' }}"
            >
                Semua Kategori
            </button>
            @foreach ($categories as $cat)
                <button 
                    wire:click="selectCategory({{ $cat->id }})" 
                    class="shrink-0 flex items-center justify-center min-h-[44px] px-5 py-2 text-sm font-bold rounded-full border transition-all duration-350 ease-out-quint hover:scale-[1.02] active:scale-[0.98] {{ $selectedCategoryId === $cat->id ? 'bg-stone-950 border-stone-950 text-white dark:bg-white dark:border-white dark:text-stone-950 shadow-sm' : 'bg-white border-stone-200 text-stone-700 hover:border-stone-300 dark:bg-stone-900 dark:border-stone-800 dark:text-stone-350 dark:hover:border-stone-700' }}"
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
                class="group flex flex-col h-full bg-white dark:bg-stone-950 border border-stone-200 dark:border-stone-850 rounded-3xl overflow-hidden hover:shadow-lg hover:-translate-y-1 hover:border-accent/30 dark:hover:border-accent/30 transition-all duration-350 ease-out-quint relative"
                wire:navigate
            >
                <!-- Thumbnail Image -->
                <div class="aspect-square w-full bg-stone-50 dark:bg-stone-900 relative overflow-hidden">
                    @if ($primaryImage)
                        <img 
                            src="{{ asset('storage/' . $primaryImage->path) }}" 
                            alt="{{ $product->name }}" 
                            class="h-full w-full object-cover group-hover:scale-[1.03] transition-transform duration-350 ease-out-quint"
                        />
                    @else
                        <div class="h-full w-full flex items-center justify-center text-stone-400">
                            <flux:icon icon="photo" class="h-10 w-10 text-stone-300 dark:text-stone-700" />
                        </div>
                    @endif

                    <!-- Featured/SaleMode Badges on Thumbnail -->
                    <div class="absolute top-3.5 left-3.5 flex flex-col gap-1.5 items-start">
                        @if ($product->is_featured)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[10px] font-black tracking-wider uppercase bg-amber-500 text-white shadow-xs">
                                <flux:icon icon="star" class="h-3 w-3" />
                                Rekomendasi
                            </span>
                        @endif

                        @if ($product->sale_mode->value === 'preorder')
                            <span class="inline-flex px-2.5 py-1 rounded-xl text-[10px] font-black tracking-wider uppercase bg-accent text-white shadow-xs">
                                Pre-Order
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Info Body -->
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div class="space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-accent block">
                            {{ $product->category->name }}
                        </span>
                        <h3 class="font-extrabold text-stone-900 dark:text-white group-hover:text-accent dark:group-hover:text-accent transition-colors duration-300 text-base leading-snug line-clamp-1">
                            {{ $product->name }}
                        </h3>
                        <p class="text-xs text-stone-500 dark:text-stone-400 line-clamp-2 leading-relaxed">
                            {{ $product->description ?: 'Tidak ada deskripsi.' }}
                        </p>
                    </div>

                    <div class="pt-4 mt-4 border-t border-stone-100 dark:border-stone-850 flex items-center justify-between">
                        <div>
                            <span class="text-[9px] text-stone-400 uppercase tracking-widest block font-bold">Mulai Dari</span>
                            <span class="font-mono text-base font-black text-stone-900 dark:text-white">
                                Rp{{ number_format($minPrice, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        <div class="h-9 w-9 rounded-xl bg-stone-50 dark:bg-stone-900 border border-stone-150 dark:border-stone-800 flex items-center justify-center group-hover:bg-accent group-hover:text-white dark:group-hover:bg-accent dark:group-hover:text-stone-950 group-hover:border-accent transition-all duration-300 ease-out-quint">
                            <flux:icon icon="chevron-right" class="h-5 w-5" />
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full py-16 text-center text-stone-400 dark:text-stone-600 border border-dashed border-stone-200 dark:border-stone-800 rounded-3xl bg-white dark:bg-stone-950/40">
                <flux:icon icon="squares-plus" class="h-12 w-12 mx-auto text-stone-300 dark:text-stone-700 mb-3" />
                <p class="font-bold text-stone-750 dark:text-stone-300">Tidak ada produk ditemukan.</p>
                <p class="text-sm mt-1 text-stone-500">Coba ubah kata kunci pencarian Anda.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-6">
        {{ $products->links() }}
    </div>
</div>
