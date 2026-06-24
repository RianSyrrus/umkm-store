<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
        <a href="{{ route('home') }}" class="hover:text-zinc-800 dark:hover:text-white" wire:navigate>Katalog</a>
        <flux:icon icon="chevron-right" class="h-3 w-3" />
        <span class="text-zinc-400">Keranjang Belanja</span>
    </div>

    <div class="flex items-center justify-between">
        <flux:heading size="xl">Keranjang Belanja</flux:heading>
    </div>

    @if ($items->isEmpty())
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-12 text-center space-y-4 shadow-sm">
            <div class="h-16 w-16 bg-zinc-50 dark:bg-zinc-900 rounded-2xl flex items-center justify-center mx-auto border border-zinc-150 dark:border-zinc-800">
                <flux:icon icon="shopping-bag" class="h-8 w-8 text-zinc-400" />
            </div>
            <div class="space-y-1">
                <flux:heading size="lg">Keranjang Anda Kosong</flux:heading>
                <flux:text>Anda belum menambahkan produk apa pun ke keranjang belanja Anda.</flux:text>
            </div>
            <div class="pt-2">
                <flux:button as="a" :href="route('home')" variant="primary" wire:navigate>Kembali ke Katalog</flux:button>
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
                    <div class="flex flex-col sm:flex-row gap-4 p-5 bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm relative">
                        <!-- Thumbnail Image -->
                        <div class="h-20 w-20 rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 flex-shrink-0">
                            @if ($primaryImage)
                                <img src="{{ asset('storage/' . $primaryImage->path) }}" class="h-full w-full object-cover" />
                            @else
                                <div class="h-full w-full flex items-center justify-center text-zinc-400">
                                    <flux:icon icon="photo" class="h-6 w-6" />
                                </div>
                            @endif
                        </div>

                        <!-- Item Info -->
                        <div class="flex-1 space-y-2">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <h3 class="font-bold text-zinc-950 dark:text-white leading-tight">
                                        {{ $product->name }}
                                    </h3>
                                    <span class="text-xs text-zinc-500 font-medium">Varian: {{ $item['variant']->name }}</span>
                                </div>
                                <span class="font-mono text-base font-extrabold text-zinc-950 dark:text-white">
                                    Rp{{ number_format($item['total_price'], 0, ',', '.') }}
                                </span>
                            </div>

                            <!-- Options & Addons Lists -->
                            @if ($item['options']->isNotEmpty() || $item['addons']->isNotEmpty() || !empty($item['notes']))
                                <div class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-xl space-y-1.5 border border-zinc-150 dark:border-zinc-800 text-xs">
                                    @foreach ($item['options'] as $opt)
                                        <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                                            <span>{{ $opt->optionGroup->name }}: <span class="font-bold">{{ $opt->name }}</span></span>
                                            @if($opt->price_delta !== 0)
                                                <span class="font-mono text-zinc-500">{{ $opt->price_delta > 0 ? '+' : '' }}Rp{{ number_format($opt->price_delta, 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    @endforeach

                                    @foreach ($item['addons'] as $addon)
                                        <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                                            <span>Add-on: <span class="font-bold">{{ $addon->name }}</span></span>
                                            <span class="font-mono text-zinc-500">+Rp{{ number_format($addon->price, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach

                                    @if (!empty($item['notes']))
                                        <div class="text-zinc-500 italic pt-1 border-t border-zinc-200/50 dark:border-zinc-700/50">
                                            Catatan: "{{ $item['notes'] }}"
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Quantity Adjuster & Remove -->
                            <div class="flex items-center justify-between pt-2">
                                <div class="flex items-center gap-2 bg-zinc-50 dark:bg-zinc-900 p-1 rounded-xl border border-zinc-150 dark:border-zinc-800">
                                    <flux:button 
                                        type="button" 
                                        wire:click="decrement('{{ $id }}')" 
                                        icon="minus" 
                                        variant="ghost" 
                                        size="sm" 
                                        class="!h-7 !w-7"
                                    />
                                    <span class="w-8 text-center font-mono text-xs font-bold text-zinc-950 dark:text-white">
                                        {{ $item['quantity'] }}
                                    </span>
                                    <flux:button 
                                        type="button" 
                                        wire:click="increment('{{ $id }}')" 
                                        icon="plus" 
                                        variant="ghost" 
                                        size="sm" 
                                        class="!h-7 !w-7"
                                    />
                                </div>

                                <flux:button 
                                    type="button" 
                                    wire:click="removeItem('{{ $id }}')" 
                                    icon="trash" 
                                    variant="ghost" 
                                    color="red" 
                                    size="sm"
                                    title="Hapus Item"
                                />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Summary Card (Right Column) -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-6 shadow-sm space-y-6">
                <div>
                    <flux:heading size="lg">Ringkasan Belanja</flux:heading>
                    <flux:text>Total biaya pesanan Anda.</flux:text>
                </div>

                <div class="space-y-3 text-sm border-t border-b border-zinc-150 dark:border-zinc-800 py-4">
                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                        <span>Total Harga ({{ $items->sum('quantity') }} Item)</span>
                        <span class="font-mono text-zinc-950 dark:text-white">Rp{{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                        <span>Biaya Pengiriman</span>
                        <span class="text-xs italic text-zinc-400">Dihitung saat checkout</span>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="font-bold text-zinc-900 dark:text-white">Subtotal</span>
                    <span class="font-mono text-xl font-extrabold text-zinc-900 dark:text-white">
                        Rp{{ number_format($total, 0, ',', '.') }}
                    </span>
                </div>

                <flux:button 
                    as="a"
                    :href="route('home.checkout')"
                    variant="primary" 
                    class="w-full font-bold py-3 text-center block"
                    wire:navigate
                >
                    Lanjut ke Pembayaran
                </flux:button>
            </div>
        </div>
    @endif
</div>
