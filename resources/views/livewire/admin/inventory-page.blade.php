<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Manajemen Stok</flux:heading>
            <flux:text>Pantau ketersediaan stok produk dan lakukan penyesuaian stok secara manual.</flux:text>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 space-y-4">
        <!-- Pencarian & Filter -->
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <div class="flex-1 max-w-sm">
                <flux:input 
                    wire:model.live="search" 
                    placeholder="Cari SKU atau nama produk..." 
                    icon="magnifying-glass" 
                    clearable 
                />
            </div>
            
            <div class="flex items-center gap-2">
                <flux:checkbox 
                    wire:model.live="onlyLowStock" 
                    label="Tampilkan Stok Menipis Saja" 
                />
            </div>
        </div>

        <!-- Tabel Stok -->
        <flux:table>
            <flux:table.columns>
                <flux:table.column>SKU</flux:table.column>
                <flux:table.column>Nama Produk & Varian</flux:table.column>
                <flux:table.column>Stok Fisik (On Hand)</flux:table.column>
                <flux:table.column>Stok Terpesan</flux:table.column>
                <flux:table.column>Stok Tersedia</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column class="text-right">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($variants as $var)
                    @php
                        $available = $var->stock_on_hand - $var->reserved_stock;
                        $isLow = $available <= $threshold;
                    @endphp
                    <flux:table.row :key="$var->id">
                        <flux:table.cell class="font-mono text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $var->sku }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium text-zinc-900 dark:text-white">
                                {{ $var->product->name }}
                            </div>
                            <div class="text-xs text-zinc-500">
                                Varian: {{ $var->name }}
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="font-mono text-sm text-zinc-700 dark:text-zinc-300">
                            {{ $var->stock_on_hand }}
                        </flux:table.cell>
                        <flux:table.cell class="font-mono text-sm text-zinc-500">
                            {{ $var->reserved_stock }}
                        </flux:table.cell>
                        <flux:table.cell class="font-mono text-sm font-bold {{ $isLow ? 'text-red-600' : 'text-zinc-900 dark:text-white' }}">
                            {{ $available }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($isLow)
                                <flux:badge color="red" size="sm">Stok Menipis</flux:badge>
                            @else
                                <flux:badge color="green" size="sm">Cukup</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-right">
                            <flux:button 
                                wire:click="openAdjustment({{ $var->id }})" 
                                size="sm" 
                                icon="adjustments-horizontal" 
                                variant="ghost"
                                title="Sesuaikan Stok"
                            >
                                Sesuaikan
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8 text-zinc-400">
                            Tidak ada varian produk ditemukan.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-4">
            {{ $variants->links() }}
        </div>
    </div>

    <!-- Modal Penyesuaian Stok -->
    @if ($showAdjustmentModal && $selectedVariant)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-md bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-zinc-150 dark:border-zinc-700">
                    <flux:heading size="lg">Penyesuaian Stok</flux:heading>
                    <flux:text class="mt-1">
                        Sesuaikan stok untuk **{{ $selectedVariant->product->name }}** ({{ $selectedVariant->name }})
                    </flux:text>
                </div>

                <form wire:submit.prevent="submitAdjustment" class="p-6 space-y-4">
                    <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-150 dark:border-zinc-700">
                        <div>
                            <span class="text-xs text-zinc-500 uppercase tracking-wider block font-semibold">Stok Saat Ini</span>
                            <span class="font-mono text-xl font-bold text-zinc-900 dark:text-white">{{ $selectedVariant->stock_on_hand }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-zinc-500 uppercase tracking-wider block font-semibold">Tersedia</span>
                            <span class="font-mono text-xl font-bold text-zinc-900 dark:text-white">{{ $selectedVariant->stock_on_hand - $selectedVariant->reserved_stock }}</span>
                        </div>
                    </div>

                    <flux:field>
                        <flux:label>Jenis Penyesuaian</flux:label>
                        <flux:select wire:model="adjustType">
                            <option value="restock">Restock (Stok Masuk)</option>
                            <option value="adjustment">Manual Adjustment (Opname/Koreksi)</option>
                            <option value="waste">Waste (Buang/Rusak/Kedaluwarsa)</option>
                        </flux:select>
                        <flux:error name="adjustType" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Jumlah Penyesuaian (Delta)</flux:label>
                        <flux:input 
                            type="number" 
                            wire:model="adjustQuantity" 
                            placeholder="Gunakan tanda minus (-) untuk mengurangi" 
                        />
                        <flux:text size="sm" class="mt-1 block text-zinc-500">
                            Contoh: **10** (menambah 10) atau **-5** (mengurangi 5).
                        </flux:text>
                        <flux:error name="adjustQuantity" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Alasan Penyesuaian</flux:label>
                        <flux:input 
                            wire:model="adjustReason" 
                            placeholder="Contoh: Barang datang dari supplier, Opname akhir bulan" 
                        />
                        <flux:error name="adjustReason" />
                    </flux:field>

                    <div class="pt-4 border-t border-zinc-150 dark:border-zinc-700 flex justify-end gap-3">
                        <flux:button type="button" variant="ghost" wire:click="closeAdjustment">Batal</flux:button>
                        <flux:button type="submit" variant="primary">Simpan</flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
