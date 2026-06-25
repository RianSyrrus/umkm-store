<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm text-stone-500 dark:text-stone-400">
        <a href="{{ route('home') }}" class="hover:text-accent dark:hover:text-white font-medium" wire:navigate>Katalog</a>
        <flux:icon icon="chevron-right" class="h-3 w-3" />
        <span class="text-stone-400 dark:text-stone-500 font-semibold">Keranjang Belanja</span>
    </div>

    <div class="flex items-center justify-between">
        <flux:heading size="xl" class="font-black text-stone-950 dark:text-white tracking-tight">Keranjang Belanja</flux:heading>
    </div>

    @if ($items->isEmpty())
        <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-12 text-center space-y-5 shadow-sm">
            <div class="h-16 w-16 bg-stone-50 dark:bg-stone-900 rounded-2xl flex items-center justify-center mx-auto border border-stone-150 dark:border-stone-800">
                <flux:icon icon="shopping-bag" class="h-8 w-8 text-stone-400 dark:text-stone-500" />
            </div>
            <div class="space-y-1.5">
                <flux:heading size="lg" class="font-black text-stone-950 dark:text-white">Keranjang Anda Kosong</flux:heading>
                <flux:text class="text-stone-500">Anda belum menambahkan produk apa pun ke keranjang belanja Anda.</flux:text>
            </div>
            <div class="pt-2">
                <flux:button as="a" :href="route('home')" variant="primary" class="font-extrabold shadow-sm transition-transform duration-300 hover:scale-[1.02] active:scale-[0.98]" wire:navigate>Kembali ke Katalog</flux:button>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <!-- List Item (Left Columns) -->
            <div class="lg:col-span-2 space-y-4">
                @foreach ($items as $id => $item)
                    @php
                        $product = $item['product'];
                        $primaryImage = $product->images->where('is_primary', true)->first() ?: $product->images->first();
                    @endphp
                    <div class="flex flex-col sm:flex-row gap-5 p-5 bg-white dark:bg-stone-950 border border-stone-200 dark:border-stone-850 rounded-3xl shadow-sm relative">
                        <!-- Thumbnail Image -->
                        <div class="h-20 w-20 rounded-2xl overflow-hidden bg-stone-150 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 flex-shrink-0">
                            @if ($primaryImage)
                                <img src="{{ asset('storage/' . $primaryImage->path) }}" class="h-full w-full object-cover" />
                            @else
                                <div class="h-full w-full flex items-center justify-center text-stone-400">
                                    <flux:icon icon="photo" class="h-6 w-6" />
                                </div>
                            @endif
                        </div>

                        <!-- Item Info -->
                        <div class="flex-1 space-y-3">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <h3 class="font-extrabold text-stone-950 dark:text-white leading-tight text-base">
                                        {{ $product->name }}
                                    </h3>
                                    <span class="text-xs text-stone-500 dark:text-stone-400 font-bold uppercase tracking-wider block mt-1">Varian: {{ $item['variant']->name }}</span>
                                </div>
                                <span class="font-mono text-base font-black text-accent shrink-0">
                                    Rp{{ number_format($item['total_price'], 0, ',', '.') }}
                                </span>
                            </div>

                            <!-- Options & Addons Lists -->
                            @if ($item['options']->isNotEmpty() || $item['addons']->isNotEmpty() || !empty($item['notes']))
                                <div class="p-3.5 bg-stone-50 dark:bg-stone-900/60 rounded-2xl space-y-2 border border-stone-150 dark:border-stone-850 text-xs">
                                    @foreach ($item['options'] as $opt)
                                        <div class="flex justify-between text-stone-600 dark:text-stone-400 font-medium">
                                            <span>{{ $opt->optionGroup->name }}: <span class="font-extrabold text-stone-900 dark:text-white">{{ $opt->name }}</span></span>
                                            @if($opt->price_delta !== 0)
                                                <span class="font-mono text-stone-500">{{ $opt->price_delta > 0 ? '+' : '' }}Rp{{ number_format($opt->price_delta, 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    @endforeach

                                    @foreach ($item['addons'] as $addon)
                                        <div class="flex justify-between text-stone-600 dark:text-stone-400 font-medium">
                                            <span>Add-on: <span class="font-extrabold text-stone-900 dark:text-white">{{ $addon->name }}</span></span>
                                            <span class="font-mono text-stone-500">+Rp{{ number_format($addon->price, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach

                                    @if (!empty($item['notes']))
                                        <div class="text-stone-500 dark:text-stone-450 italic pt-2 border-t border-stone-200/50 dark:border-stone-800/50 font-medium">
                                            Catatan: "{{ $item['notes'] }}"
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Quantity Adjuster & Remove -->
                            <div class="flex items-center justify-between pt-2">
                                <div class="flex items-center gap-2 bg-stone-50 dark:bg-stone-900 p-1 rounded-2xl border border-stone-200 dark:border-stone-800">
                                    <flux:button 
                                        type="button" 
                                        wire:click="decrement('{{ $id }}')" 
                                        icon="minus" 
                                        variant="ghost" 
                                        class="!h-11 !w-11 rounded-xl text-stone-700 hover:text-stone-950 dark:text-stone-400 dark:hover:text-white"
                                        title="Kurangi Kuantitas"
                                    />
                                    <span class="w-10 text-center font-mono text-sm font-black text-stone-950 dark:text-white">
                                        {{ $item['quantity'] }}
                                    </span>
                                    <flux:button 
                                        type="button" 
                                        wire:click="increment('{{ $id }}')" 
                                        icon="plus" 
                                        variant="ghost" 
                                        class="!h-11 !w-11 rounded-xl text-stone-700 hover:text-stone-950 dark:text-stone-400 dark:hover:text-white"
                                        title="Tambah Kuantitas"
                                    />
                                </div>

                                <flux:button 
                                    type="button" 
                                    wire:click="removeItem('{{ $id }}')" 
                                    icon="trash" 
                                    variant="ghost" 
                                    color="red" 
                                    class="!h-11 !w-11 rounded-xl"
                                    title="Hapus Item"
                                />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Summary Card (Right Column) -->
            <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-6 shadow-sm space-y-6">
                <div>
                    <flux:heading size="lg" class="font-black text-stone-950 dark:text-white tracking-tight">Ringkasan Belanja</flux:heading>
                    <flux:text class="text-stone-500">Total biaya pesanan Anda.</flux:text>
                </div>

                <div class="space-y-3.5 text-sm border-t border-b border-stone-150 dark:border-stone-850 py-5">
                    <div class="flex justify-between text-stone-600 dark:text-stone-400 font-medium">
                        <span>Total Harga ({{ $items->sum('quantity') }} Item)</span>
                        <span class="font-mono text-stone-950 dark:text-white font-bold">Rp{{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-stone-600 dark:text-stone-400 font-medium">
                        <span>Biaya Pengiriman</span>
                        <span class="text-xs italic text-stone-450 dark:text-stone-500 font-bold">Dihitung saat checkout</span>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-stone-900 dark:text-white">Subtotal</span>
                    <span class="font-mono text-xl font-black text-accent">
                        Rp{{ number_format($total, 0, ',', '.') }}
                    </span>
                </div>

                <flux:button 
                    as="a"
                    :href="route('home.checkout')"
                    variant="primary" 
                    class="w-full font-extrabold py-3 text-center block shadow-sm transition-transform duration-300 hover:scale-[1.02] active:scale-[0.98] min-h-[44px]"
                    wire:navigate
                >
                    Lanjut ke Pembayaran
                </flux:button>
            </div>
        </div>
    @endif
</div>
