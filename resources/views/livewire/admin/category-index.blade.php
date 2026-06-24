<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Kelola Kategori</flux:heading>
            <flux:text>Kelola kategori produk untuk menu atau katalog toko Anda.</flux:text>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- List Kategori -->
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center justify-between mb-4 gap-4">
                    <div class="flex-1 max-w-sm">
                        <flux:input 
                            wire:model.live="search" 
                            placeholder="Cari kategori..." 
                            icon="magnifying-glass" 
                            clearable 
                        />
                    </div>
                </div>

                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Urutan</flux:table.column>
                        <flux:table.column>Nama Kategori</flux:table.column>
                        <flux:table.column>Deskripsi</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column class="text-right">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($categories as $category)
                            <flux:table.row :key="$category->id">
                                <flux:table.cell>
                                    <span class="font-mono text-xs text-zinc-500">#{{ $category->sort_order }}</span>
                                </flux:table.cell>
                                <flux:table.cell class="font-medium text-zinc-900 dark:text-white">
                                    {{ $category->name }}
                                </flux:table.cell>
                                <flux:table.cell class="max-w-xs truncate text-zinc-600 dark:text-zinc-400">
                                    {{ $category->description ?: '-' }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($category->is_active)
                                        <flux:badge color="green" size="sm">Aktif</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">Nonaktif</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button 
                                            wire:click="editCategory({{ $category->id }})" 
                                            icon="pencil" 
                                            variant="ghost" 
                                            size="sm" 
                                            title="Ubah Kategori" 
                                        />
                                        <flux:button 
                                            wire:click="deleteCategory({{ $category->id }})" 
                                            wire:confirm="Apakah Anda yakin ingin menghapus kategori ini?"
                                            icon="trash" 
                                            variant="ghost" 
                                            size="sm" 
                                            color="red" 
                                            title="Hapus Kategori" 
                                        />
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center py-8 text-zinc-400">
                                    Tidak ada kategori ditemukan.
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>

                <div class="mt-4">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>

        <!-- Form Tambah / Edit Kategori -->
        <div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ $editingCategoryId ? 'Ubah Kategori' : 'Tambah Kategori Baru' }}
                    </flux:heading>
                    <flux:text>
                        {{ $editingCategoryId ? 'Perbarui informasi kategori terpilih.' : 'Buat kategori baru untuk produk Anda.' }}
                    </flux:text>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <flux:field>
                        <flux:label>Nama Kategori</flux:label>
                        <flux:input wire:model="name" placeholder="Contoh: Makanan Utama, Minuman" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Deskripsi</flux:label>
                        <flux:textarea wire:model="description" placeholder="Deskripsi singkat mengenai kategori" rows="3" />
                        <flux:error name="description" />
                    </flux:field>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Nomor Urut Tampil</flux:label>
                            <flux:input type="number" wire:model="sort_order" min="0" />
                            <flux:error name="sort_order" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Status</flux:label>
                            <div class="mt-2 flex items-center gap-2">
                                <flux:checkbox wire:model="is_active" label="Aktif" />
                            </div>
                            <flux:error name="is_active" />
                        </flux:field>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                        @if ($editingCategoryId)
                            <flux:button type="button" wire:click="resetInputFields" variant="ghost">Batal</flux:button>
                        @endif
                        <flux:button type="submit" variant="primary">
                            {{ $editingCategoryId ? 'Simpan Perubahan' : 'Tambah Kategori' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
