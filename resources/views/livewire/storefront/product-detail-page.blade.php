<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
        <a href="{{ route('home') }}" class="hover:text-zinc-800 dark:hover:text-white" wire:navigate>Katalog</a>
        <flux:icon icon="chevron-right" class="h-3 w-3" />
        <span class="text-zinc-400 truncate">{{ $product->name }}</span>
    </div>

    <!-- Product Layout -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
        <!-- Gallery (Left Column) -->
        <div class="space-y-4">
            <!-- Active Image -->
            <div class="aspect-square w-full rounded-2xl overflow-hidden bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 relative">
                @if ($activeImagePath)
                    <img 
                        src="{{ asset('storage/' . $activeImagePath) }}" 
                        alt="{{ $product->name }}" 
                        class="h-full w-full object-cover"
                    />
                @else
                    <div class="h-full w-full flex items-center justify-center text-zinc-400">
                        <flux:icon icon="photo" class="h-12 w-12 text-zinc-300 dark:text-zinc-700" />
                    </div>
                @endif

                @if ($product->sale_mode->value === 'preorder')
                    <span class="absolute top-4 left-4 inline-flex px-3 py-1 rounded-lg text-xs font-bold tracking-wider uppercase bg-indigo-600 text-white shadow-sm">
                        Pre-Order
                    </span>
                @endif
            </div>

            <!-- Thumbnails -->
            @if ($product->images->count() > 1)
                <div class="flex gap-3 overflow-x-auto pb-1">
                    @foreach ($product->images as $img)
                        <button 
                            wire:click="selectImage('{{ $img->path }}')"
                            class="h-16 w-16 rounded-xl overflow-hidden border-2 flex-shrink-0 transition-all duration-200 {{ $activeImagePath === $img->path ? 'border-zinc-900 dark:border-white shadow-sm' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300' }}"
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
            <div class="space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    {{ $product->category->name }}
                </span>
                <h1 class="text-3xl font-extrabold text-zinc-950 dark:text-white leading-tight">
                    {{ $product->name }}
                </h1>
                
                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">
                    {{ $product->description ?: 'Tidak ada deskripsi.' }}
                </p>
            </div>

            <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6 space-y-6">
                <!-- Varian (Required Selection) -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-zinc-900 dark:text-white uppercase tracking-wider">
                            Pilih Varian <span class="text-red-500">*</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        @foreach ($product->variants as $var)
                            @if ($var->is_active)
                                <label 
                                    class="flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 {{ $variantId === $var->id ? 'border-zinc-900 bg-zinc-50/50 dark:border-white dark:bg-zinc-800/40 shadow-sm' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300' }}"
                                >
                                    <div class="flex items-center gap-3">
                                        <input 
                                            type="radio" 
                                            name="variant" 
                                            value="{{ $var->id }}" 
                                            wire:model.live="variantId" 
                                            class="text-zinc-900 focus:ring-zinc-900 dark:text-white dark:focus:ring-white h-4 w-4 border-zinc-300"
                                        />
                                        <div>
                                            <span class="font-bold text-sm text-zinc-900 dark:text-white block">{{ $var->name }}</span>
                                            @if($product->sale_mode->value === 'ready_stock' || $product->sale_mode->value === 'both')
                                                <span class="text-xs text-zinc-500 font-mono">Sisa Stok: {{ $var->stock_on_hand }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="font-mono text-sm font-extrabold text-zinc-900 dark:text-white">
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
                            <span class="text-sm font-bold text-zinc-900 dark:text-white uppercase tracking-wider">
                                {{ $group->name }}
                                @if ($group->is_required)
                                    <span class="text-red-500">*</span>
                                @endif
                            </span>
                            @if ($group->max_selected > 1)
                                <span class="text-xs text-zinc-400">Pilih maks {{ $group->max_selected }}</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 gap-2.5">
                            @foreach ($group->values as $val)
                                @if ($val->is_active)
                                    @php
                                        $priceDelta = $val->price_delta;
                                    @endphp
                                    <label 
                                        class="flex items-center justify-between p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/20 transition-all duration-200"
                                    >
                                        <div class="flex items-center gap-3">
                                            @if ($group->max_selected === 1)
                                                <input 
                                                    type="radio" 
                                                    name="option_{{ $group->id }}" 
                                                    value="{{ $val->id }}" 
                                                    wire:model.live="selectedOptions.{{ $group->id }}.0" 
                                                    class="text-zinc-900 focus:ring-zinc-900 h-4 w-4 border-zinc-300"
                                                />
                                            @else
                                                <input 
                                                    type="checkbox" 
                                                    value="{{ $val->id }}" 
                                                    wire:model.live="selectedOptions.{{ $group->id }}" 
                                                    class="text-zinc-900 rounded focus:ring-zinc-900 h-4 w-4 border-zinc-300"
                                                />
                                            @endif
                                            <span class="text-sm text-zinc-800 dark:text-zinc-200 font-medium">{{ $val->name }}</span>
                                        </div>
                                        @if ($priceDelta !== 0)
                                            <span class="font-mono text-xs font-semibold text-zinc-500">
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
                        <span class="text-sm font-bold text-zinc-900 dark:text-white uppercase tracking-wider block">
                            Add-ons (Item Tambahan)
                        </span>

                        <div class="grid grid-cols-1 gap-2.5">
                            @foreach ($product->addons as $addon)
                                @if ($addon->is_active)
                                    <label 
                                        class="flex items-center justify-between p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/20 transition-all duration-200"
                                    >
                                        <div class="flex items-center gap-3">
                                            <input 
                                                type="checkbox" 
                                                value="{{ $addon->id }}" 
                                                wire:model.live="selectedAddons" 
                                                class="text-zinc-900 rounded focus:ring-zinc-900 h-4 w-4 border-zinc-300"
                                            />
                                            <span class="text-sm text-zinc-800 dark:text-zinc-200 font-medium">{{ $addon->name }}</span>
                                        </div>
                                        <span class="font-mono text-xs font-semibold text-zinc-500">
                                            +Rp{{ number_format($addon->price, 0, ',', '.') }}
                                        </span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Quantity & Add to Cart -->
                <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-zinc-900 dark:text-white uppercase tracking-wider">
                            Jumlah Item
                        </span>
                        
                        <div class="flex items-center gap-3 bg-zinc-100 dark:bg-zinc-800 p-1.5 rounded-xl">
                            <flux:button 
                                type="button" 
                                wire:click="decrementQuantity" 
                                icon="minus" 
                                variant="ghost" 
                                size="sm" 
                                class="!h-8 !w-8"
                            />
                            <span class="w-8 text-center font-mono text-sm font-extrabold text-zinc-900 dark:text-white">
                                {{ $quantity }}
                            </span>
                            <flux:button 
                                type="button" 
                                wire:click="incrementQuantity" 
                                icon="plus" 
                                variant="ghost" 
                                size="sm" 
                                class="!h-8 !w-8"
                            />
                        </div>
                    </div>

                    <!-- Pricing Summary & Button -->
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <span class="text-[10px] text-zinc-400 uppercase tracking-wider block font-semibold">Estimasi Total</span>
                            <span class="font-mono text-2xl font-black text-zinc-900 dark:text-white">
                                Rp{{ number_format($totalPrice, 0, ',', '.') }}
                            </span>
                        </div>
                        <flux:button 
                            type="button" 
                            wire:click="submitConfiguration" 
                            variant="primary" 
                            class="font-extrabold flex-1 sm:flex-none py-3 px-6 shadow-sm"
                        >
                            Masukkan Keranjang
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
