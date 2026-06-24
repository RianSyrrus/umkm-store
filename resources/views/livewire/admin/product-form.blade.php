<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $product ? 'Ubah Produk' : 'Tambah Produk Baru' }}</flux:heading>
            <flux:text>{{ $product ? 'Perbarui informasi detail, varian harga, dan opsi produk.' : 'Buat produk baru dengan mendefinisikan informasi, varian, dan opsi penambahan.' }}</flux:text>
        </div>
        <flux:button as="a" :href="route('admin.products')" variant="ghost" icon="arrow-left" wire:navigate>Kembali</flux:button>
    </div>

    <form wire:submit="save" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Form (Left 2 Columns) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informasi Produk -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 space-y-4">
                <flux:heading size="lg">Informasi Dasar</flux:heading>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>Nama Produk</flux:label>
                        <flux:input wire:model="name" placeholder="Contoh: Nasi Goreng Spesial" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Kategori</flux:label>
                        <flux:select wire:model="categoryId" placeholder="Pilih Kategori">
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="categoryId" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Deskripsi Produk</flux:label>
                    <flux:textarea wire:model="description" placeholder="Tuliskan deskripsi lengkap produk..." rows="5" />
                    <flux:error name="description" />
                </flux:field>
            </div>

            <!-- Varian Produk -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">Varian Produk</flux:heading>
                        <flux:text>Definisikan minimal satu varian ukuran/porsi beserta harga dan stoknya.</flux:text>
                    </div>
                    <flux:button type="button" wire:click="addVariantRow" icon="plus" size="sm" variant="ghost">Tambah Varian</flux:button>
                </div>

                @if($errors->has('variants'))
                    <div class="text-sm text-red-600 font-medium p-3 bg-red-55 dark:bg-red-950/20 rounded-lg">
                        {{ $errors->first('variants') }}
                    </div>
                @endif

                <div class="space-y-4">
                    @foreach ($variants as $index => $variant)
                        <div class="flex flex-col md:flex-row gap-4 p-4 border border-zinc-150 dark:border-zinc-700 rounded-xl relative bg-zinc-50/50 dark:bg-zinc-800/40">
                            <!-- Varian Name -->
                            <div class="flex-1">
                                <flux:field>
                                    <flux:label>Nama Varian</flux:label>
                                    <flux:input wire:model="variants.{{ $index }}.name" placeholder="Misal: Porsi Biasa, Jumbo" />
                                    <flux:error name="variants.{{ $index }}.name" />
                                </flux:field>
                            </div>

                            <!-- SKU -->
                            <div class="w-full md:w-40">
                                <flux:field>
                                    <flux:label>SKU</flux:label>
                                    <flux:input wire:model="variants.{{ $index }}.sku" placeholder="Misal: NASGOR-REG" />
                                    <flux:error name="variants.{{ $index }}.sku" />
                                </flux:field>
                            </div>

                            <!-- Harga -->
                            <div class="w-full md:w-44">
                                <flux:field>
                                    <flux:label>Harga (Rp)</flux:label>
                                    <flux:input type="number" wire:model="variants.{{ $index }}.price" min="0" />
                                    <flux:error name="variants.{{ $index }}.price" />
                                </flux:field>
                            </div>

                            <!-- Stock -->
                            <div class="w-full md:w-32">
                                <flux:field>
                                    <flux:label>Stok Awal</flux:label>
                                    <flux:input type="number" wire:model="variants.{{ $index }}.stock_on_hand" min="0" />
                                    <flux:error name="variants.{{ $index }}.stock_on_hand" />
                                </flux:field>
                            </div>

                            <!-- Delete Button -->
                            @if (count($variants) > 1)
                                <div class="flex items-end justify-end md:pb-2">
                                    <flux:button 
                                        type="button" 
                                        wire:click="removeVariantRow({{ $index }})" 
                                        icon="trash" 
                                        variant="ghost" 
                                        color="red" 
                                        size="sm" 
                                        title="Hapus Varian" 
                                    />
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar / Settings (Right Column) -->
        <div class="space-y-6">
            <!-- Publikasi & Status -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 space-y-4">
                <flux:heading size="lg">Status & Mode</flux:heading>

                <flux:field>
                    <flux:label>Mode Penjualan</flux:label>
                    <flux:select wire:model="saleMode">
                        <option value="ready_stock">Ready Stock (Hanya dari stok toko)</option>
                        <option value="preorder">Pre-Order (Tanpa cek stok)</option>
                        <option value="both">Both (Bisa ready/preorder)</option>
                    </flux:select>
                    <flux:error name="saleMode" />
                </flux:field>

                <div class="space-y-3 pt-2">
                    <flux:checkbox wire:model="isActive" label="Aktif di Toko (Dapat Dipesan)" />
                    <flux:error name="isActive" />

                    <flux:checkbox wire:model="isFeatured" label="Rekomendasikan Produk (Featured)" />
                    <flux:error name="isFeatured" />
                </div>
            </div>

            <!-- Opsi Tambahan / Pilihan Rasa/Ukuran -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 space-y-4">
                <flux:heading size="lg">Grup Opsi (Variasi)</flux:heading>
                <flux:text>Pilih grup variasi opsional/wajib untuk produk ini (misal: Level Pedas, Level Gula).</flux:text>

                @if ($optionGroups->isEmpty())
                    <div class="text-sm text-zinc-400">
                        Belum ada grup opsi yang dikonfigurasi di sistem.
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($optionGroups as $og)
                            <flux:checkbox 
                                wire:model="selectedOptionGroups" 
                                value="{{ $og->id }}" 
                                label="{{ $og->name }} ({{ $og->is_required ? 'Wajib' : 'Opsional' }})" 
                            />
                        @endforeach
                    </div>
                @endif
                <flux:error name="selectedOptionGroups" />
            </div>

            <!-- Add-ons / Topping -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 space-y-4">
                <flux:heading size="lg">Add-ons (Topping/Item Tambahan)</flux:heading>
                <flux:text>Pilih item tambahan yang bisa dibeli bersama produk ini.</flux:text>

                @if ($addons->isEmpty())
                    <div class="text-sm text-zinc-400">
                        Belum ada add-on yang dikonfigurasi di sistem.
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($addons as $addon)
                            <flux:checkbox 
                                wire:model="selectedAddons" 
                                value="{{ $addon->id }}" 
                                label="{{ $addon->name }} (+Rp{{ number_format($addon->price, 0, ',', '.') }})" 
                            />
                        @endforeach
                    </div>
                @endif
                <flux:error name="selectedAddons" />
            </div>

            <!-- Foto Produk -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 space-y-4">
                <flux:heading size="lg">Foto Produk</flux:heading>

                <!-- Input upload -->
                <flux:field>
                    <flux:label>Pilih File Foto (Bisa memilih beberapa)</flux:label>
                    <input 
                        type="file" 
                        wire:model="newImages" 
                        multiple 
                        accept="image/*"
                        class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-zinc-50 file:text-zinc-700 hover:file:bg-zinc-100 dark:file:bg-zinc-700 dark:file:text-zinc-300 cursor-pointer" 
                    />
                    <flux:error name="newImages.*" />
                </flux:field>

                <!-- Menampilkan File yang Baru Diunggah (Preview) -->
                @if (!empty($newImages))
                    <div class="space-y-2">
                        <flux:text size="sm" class="font-medium">Preview Foto Baru:</flux:text>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($newImages as $img)
                                <div class="relative h-20 rounded-lg overflow-hidden border border-zinc-200">
                                    <img src="{{ $img->temporaryUrl() }}" class="h-full w-full object-cover" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Menampilkan Foto yang Sudah Ada -->
                @if (!empty($existingImages))
                    <div class="space-y-2 pt-2 border-t border-zinc-100 dark:border-zinc-700">
                        <flux:text size="sm" class="font-medium">Foto Terdaftar:</flux:text>
                        <div class="grid grid-cols-1 gap-3">
                            @foreach ($existingImages as $img)
                                <div class="flex items-center justify-between p-2 border border-zinc-150 dark:border-zinc-700 rounded-lg bg-zinc-50/50 dark:bg-zinc-800/40">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ asset('storage/' . $img['path']) }}" class="h-12 w-12 rounded object-cover" />
                                        @if ($img['is_primary'])
                                            <flux:badge color="green" size="sm">Utama</flux:badge>
                                        @endif
                                    </div>
                                    <div class="flex gap-2">
                                        @if (!$img['is_primary'])
                                            <flux:button 
                                                type="button" 
                                                wire:click="setPrimaryImage({{ $img['id'] }})" 
                                                size="sm" 
                                                variant="ghost"
                                            >
                                                Jadikan Utama
                                            </flux:button>
                                        @endif
                                        <flux:button 
                                            type="button" 
                                            wire:click="deleteExistingImage({{ $img['id'] }})" 
                                            icon="trash" 
                                            color="red" 
                                            variant="ghost" 
                                            size="sm"
                                        />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Tombol Aksi Simpan -->
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3">
                <flux:button as="a" :href="route('admin.products')" variant="ghost" wire:navigate>Batal</flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    {{ $product ? 'Simpan Perubahan' : 'Buat Produk' }}
                </flux:button>
            </div>
        </div>
    </form>
</div>
