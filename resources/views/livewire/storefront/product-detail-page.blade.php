<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-sm text-stone-500 dark:text-stone-400">
        <a href="{{ route('home') }}" class="hover:text-accent dark:hover:text-white font-medium" wire:navigate>Katalog</a>
        <flux:icon icon="chevron-right" class="h-3 w-3" />
        <span class="text-stone-400 dark:text-stone-500 truncate font-semibold">{{ $product->name }}</span>
    </div>

    <!-- Product Layout -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
        <!-- Gallery (Left Column) -->
        <div class="space-y-4">
            <!-- Active Image -->
            <div class="aspect-square w-full rounded-3xl overflow-hidden bg-white dark:bg-stone-950 border border-stone-200 dark:border-stone-850 relative shadow-sm">
                @if ($activeImagePath)
                    <img 
                        src="{{ asset('storage/' . $activeImagePath) }}" 
                        alt="{{ $product->name }}" 
                        class="h-full w-full object-cover"
                    />
                @else
                    <div class="h-full w-full flex items-center justify-center text-stone-400">
                        <flux:icon icon="photo" class="h-12 w-12 text-stone-300 dark:text-stone-700" />
                    </div>
                @endif

                @if ($product->sale_mode->value === 'preorder')
                    <span class="absolute top-4 left-4 inline-flex px-3 py-1 rounded-xl text-xs font-black tracking-wider uppercase bg-accent text-white shadow-xs">
                        Pre-Order
                    </span>
                @endif
            </div>

            <!-- Thumbnails -->
            @if ($product->images->count() > 1)
                <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-none">
                    @foreach ($product->images as $img)
                        <button 
                            wire:click="selectImage('{{ $img->path }}')"
                            class="h-16 w-16 rounded-2xl overflow-hidden border-2 flex-shrink-0 transition-all duration-300 ease-out-quint {{ $activeImagePath === $img->path ? 'border-accent shadow-xs scale-[0.98]' : 'border-stone-200 dark:border-stone-800 hover:border-stone-300' }} min-h-[44px] min-w-[44px]"
                            title="Pilih gambar produk"
                        >
                            <img src="{{ asset('storage/' . $img->path) }}" class="h-full w-full object-cover" />
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Info & Config (Right Column) -->
        <div class="space-y-6">
            <!-- Product Info Header -->
            <div class="space-y-3">
                <span class="text-xs font-black uppercase tracking-widest text-accent">
                    {{ $product->category->name }}
                </span>
                <h1 class="text-3xl md:text-4xl font-black text-stone-950 dark:text-white leading-tight tracking-tight">
                    {{ $product->name }}
                </h1>
                
                <p class="text-stone-600 dark:text-stone-400 text-sm leading-relaxed">
                    {{ $product->description ?: 'Tidak ada deskripsi.' }}
                </p>
            </div>

            <div class="border-t border-stone-200 dark:border-stone-850 pt-6 space-y-6">
                <!-- Varian (Required Selection) -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-stone-950 dark:text-white uppercase tracking-wider">
                            Pilih Varian <span class="text-red-500">*</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        @foreach ($product->variants as $var)
                            @if ($var->is_active)
                                <label 
                                    class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all duration-300 ease-out-quint shadow-2xs select-none {{ $variantId === $var->id ? 'border-accent bg-accent/5 dark:bg-accent/10' : 'border-stone-200 dark:border-stone-850 hover:bg-stone-50 dark:hover:bg-stone-900/40 hover:border-stone-350' }} focus-within:ring-2 focus-within:ring-accent focus-within:ring-offset-2 dark:focus-within:ring-offset-stone-950"
                                >
                                    <div class="flex items-center gap-3">
                                        <input 
                                            type="radio" 
                                            name="variant" 
                                            value="{{ $var->id }}" 
                                            wire:model.live="variantId" 
                                            class="text-accent focus:ring-accent h-4.5 w-4.5 border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 cursor-pointer"
                                        />
                                        <div>
                                            <span class="font-extrabold text-sm text-stone-950 dark:text-white block">{{ $var->name }}</span>
                                            @if($product->sale_mode->value === 'ready_stock' || $product->sale_mode->value === 'both')
                                                <span class="text-[10px] text-stone-500 font-bold uppercase tracking-wider">Sisa Stok: {{ $var->stock_on_hand }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="font-mono text-sm font-black text-stone-950 dark:text-white">
                                        Rp{{ number_format($var->price, 0, ',', '.') }}
                                    </span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Option Groups -->
                @foreach ($product->optionGroups as $group)
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-stone-950 dark:text-white uppercase tracking-wider">
                                {{ $group->name }}
                                @if ($group->is_required)
                                    <span class="text-red-500">*</span>
                                @endif
                            </span>
                            @if ($group->max_selected > 1)
                                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Pilih maks {{ $group->max_selected }}</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 gap-2.5">
                            @foreach ($group->values as $val)
                                @if ($val->is_active)
                                    @php
                                        $priceDelta = $val->price_delta;
                                        $isSelected = ($group->max_selected === 1)
                                            ? (($selectedOptions[$group->id][0] ?? null) == $val->id)
                                            : in_array($val->id, $selectedOptions[$group->id] ?? []);
                                    @endphp
                                    <label 
                                        class="flex items-center justify-between p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-300 ease-out-quint shadow-2xs select-none {{ $isSelected ? 'border-accent bg-accent/5 dark:bg-accent/10' : 'border-stone-200 dark:border-stone-850 hover:bg-stone-50 dark:hover:bg-stone-900/40 hover:border-stone-350' }} focus-within:ring-2 focus-within:ring-accent focus-within:ring-offset-2 dark:focus-within:ring-offset-stone-950"
                                    >
                                        <div class="flex items-center gap-3">
                                            @if ($group->max_selected === 1)
                                                <input 
                                                    type="radio" 
                                                    name="option_{{ $group->id }}" 
                                                    value="{{ $val->id }}" 
                                                    wire:model.live="selectedOptions.{{ $group->id }}.0" 
                                                    class="text-accent focus:ring-accent h-4.5 w-4.5 border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 cursor-pointer"
                                                />
                                            @else
                                                <input 
                                                    type="checkbox" 
                                                    value="{{ $val->id }}" 
                                                    wire:model.live="selectedOptions.{{ $group->id }}" 
                                                    class="text-accent rounded focus:ring-accent h-4.5 w-4.5 border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 cursor-pointer"
                                                />
                                            @endif
                                            <span class="text-sm text-stone-800 dark:text-stone-200 font-bold {{ $isSelected ? 'text-accent dark:text-accent' : '' }}">{{ $val->name }}</span>
                                        </div>
                                        @if ($priceDelta !== 0)
                                            <span class="font-mono text-xs font-black text-stone-500">
                                                {{ $priceDelta > 0 ? '+' : '' }}Rp{{ number_format($priceDelta, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <!-- Addons -->
                @if ($product->addons->isNotEmpty())
                    <div class="space-y-3">
                        <span class="text-xs font-black text-stone-950 dark:text-white uppercase tracking-wider block">
                            Add-ons (Item Tambahan)
                        </span>

                        <div class="grid grid-cols-1 gap-2.5">
                            @foreach ($product->addons as $addon)
                                @if ($addon->is_active)
                                    @php
                                        $isAddonSelected = in_array($addon->id, $selectedAddons);
                                    @endphp
                                    <label 
                                        class="flex items-center justify-between p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-300 ease-out-quint shadow-2xs select-none {{ $isAddonSelected ? 'border-accent bg-accent/5 dark:bg-accent/10' : 'border-stone-200 dark:border-stone-850 hover:bg-stone-50 dark:hover:bg-stone-900/40 hover:border-stone-350' }} focus-within:ring-2 focus-within:ring-accent focus-within:ring-offset-2 dark:focus-within:ring-offset-stone-950"
                                    >
                                        <div class="flex items-center gap-3">
                                            <input 
                                                type="checkbox" 
                                                value="{{ $addon->id }}" 
                                                wire:model.live="selectedAddons" 
                                                class="text-accent rounded focus:ring-accent h-4.5 w-4.5 border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 cursor-pointer"
                                            />
                                            <span class="text-sm text-stone-800 dark:text-stone-200 font-bold {{ $isAddonSelected ? 'text-accent dark:text-accent' : '' }}">{{ $addon->name }}</span>
                                        </div>
                                        <span class="font-mono text-xs font-black text-stone-500">
                                            +Rp{{ number_format($addon->price, 0, ',', '.') }}
                                        </span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Quantity & Add to Cart -->
                <div class="pt-6 border-t border-stone-200 dark:border-stone-850 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-stone-950 dark:text-white uppercase tracking-wider">
                            Jumlah Item
                        </span>
                        
                        <div class="flex items-center gap-2 bg-stone-100 dark:bg-stone-900 p-1.5 rounded-2xl border border-stone-200/60 dark:border-stone-800/60">
                            <flux:button 
                                type="button" 
                                wire:click="decrementQuantity" 
                                icon="minus" 
                                variant="ghost" 
                                class="!h-11 !w-11 rounded-xl text-stone-700 hover:text-stone-950 dark:text-stone-400 dark:hover:text-white"
                                title="Kurangi Kuantitas"
                            />
                            <span class="w-10 text-center font-mono text-base font-black text-stone-950 dark:text-white">
                                {{ $quantity }}
                            </span>
                            <flux:button 
                                type="button" 
                                wire:click="incrementQuantity" 
                                icon="plus" 
                                variant="ghost" 
                                class="!h-11 !w-11 rounded-xl text-stone-700 hover:text-stone-950 dark:text-stone-400 dark:hover:text-white"
                                title="Tambah Kuantitas"
                            />
                        </div>
                    </div>

                    <!-- Pricing Summary & Button -->
                    <div class="p-5 rounded-3xl bg-stone-50 dark:bg-stone-900 border border-stone-200 dark:border-stone-850 flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <span class="text-[9px] text-stone-450 uppercase tracking-widest block font-bold">Estimasi Total</span>
                            <span class="font-mono text-2xl font-black text-accent">
                                Rp{{ number_format($totalPrice, 0, ',', '.') }}
                            </span>
                        </div>
                        <flux:button 
                            type="button" 
                            wire:click="submitConfiguration" 
                            variant="primary" 
                            class="font-extrabold flex-1 sm:flex-none py-3 px-6 shadow-sm transition-transform duration-300 hover:scale-[1.02] active:scale-[0.98] min-h-[44px]"
                        >
                            Masukkan Keranjang
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
