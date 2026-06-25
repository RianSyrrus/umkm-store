<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="font-black text-stone-950 dark:text-white tracking-tight">Kelola Slot Jadwal</flux:heading>
            <flux:text class="text-stone-500">Atur slot waktu pengiriman dan pengambilan pre-order toko Anda.</flux:text>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- List Slot Jadwal -->
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm dark:border-stone-800 dark:bg-stone-950">
                <div class="flex items-center justify-between mb-4 gap-4">
                    <div class="flex-1 max-w-sm">
                        <flux:input 
                            wire:model.live="search" 
                            placeholder="Cari tanggal (YYYY-MM-DD)..." 
                            icon="magnifying-glass" 
                            clearable 
                            class="bg-white dark:bg-stone-950"
                        />
                    </div>
                </div>

                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Tanggal</flux:table.column>
                        <flux:table.column>Waktu</flux:table.column>
                        <flux:table.column>Batas Order</flux:table.column>
                        <flux:table.column>Kuota</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column class="text-right">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($scheduleSlots as $slot)
                            <flux:table.row :key="$slot->id">
                                <flux:table.cell class="font-bold text-stone-900 dark:text-white">
                                    {{ $slot->date->format('d M Y') }}
                                </flux:table.cell>
                                <flux:table.cell class="font-semibold text-stone-700 dark:text-stone-300">
                                    {{ $slot->timeRange }}
                                </flux:table.cell>
                                <flux:table.cell class="text-stone-600 dark:text-stone-400 text-sm">
                                    {{ $slot->order_deadline->format('d M Y, H:i') }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <span class="font-extrabold text-stone-900 dark:text-white">{{ $slot->reserved_count }}</span>
                                    <span class="text-stone-400">/</span>
                                    <span class="text-stone-600 dark:text-stone-400 font-semibold">{{ $slot->quota }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($slot->isAvailable())
                                        <flux:badge color="green" size="sm" class="font-bold">Tersedia</flux:badge>
                                    @elseif ($slot->is_active && $slot->reserved_count >= $slot->quota)
                                        <flux:badge color="amber" size="sm" class="font-bold">Penuh</flux:badge>
                                    @elseif ($slot->is_active && now()->gt($slot->order_deadline))
                                        <flux:badge color="orange" size="sm" class="font-bold">Lewat Batas</flux:badge>
                                    @else
                                        <flux:badge color="stone" size="sm" class="font-bold">Nonaktif</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button 
                                            wire:click="editSlot({{ $slot->id }})" 
                                            icon="pencil" 
                                            variant="ghost" 
                                            size="sm" 
                                            class="!h-9 !w-9 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-900"
                                            title="Ubah Slot" 
                                        />
                                        <flux:button 
                                            wire:click="deleteSlot({{ $slot->id }})" 
                                            wire:confirm="Apakah Anda yakin ingin menghapus slot jadwal ini?"
                                            icon="trash" 
                                            variant="ghost" 
                                            size="sm" 
                                            color="red" 
                                            class="!h-9 !w-9 rounded-xl hover:bg-red-50 dark:hover:bg-red-950/20"
                                            title="Hapus Slot" 
                                        />
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6" class="text-center py-10 text-stone-400 dark:text-stone-600">
                                    Tidak ada slot jadwal ditemukan.
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>

                <div class="mt-4">
                    {{ $scheduleSlots->links() }}
                </div>
            </div>
        </div>

        <!-- Form Tambah / Edit Slot -->
        <div>
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm dark:border-stone-850 dark:bg-stone-950 space-y-6">
                <div>
                    <flux:heading size="lg" class="font-black text-stone-950 dark:text-white tracking-tight">
                        {{ $editingSlotId ? 'Ubah Slot Jadwal' : 'Tambah Slot Baru' }}
                    </flux:heading>
                    <flux:text class="text-stone-500">
                        {{ $editingSlotId ? 'Perbarui informasi slot jadwal operasional terpilih.' : 'Buat slot jadwal operasional baru.' }}
                    </flux:text>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <flux:field>
                        <flux:label class="font-bold text-stone-750 dark:text-stone-300">Tanggal Slot</flux:label>
                        <flux:input type="date" wire:model="date" class="bg-white dark:bg-stone-950" />
                        <flux:error name="date" />
                    </flux:field>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label class="font-bold text-stone-750 dark:text-stone-300">Waktu Mulai</flux:label>
                            <flux:input type="time" wire:model="startTime" class="bg-white dark:bg-stone-950" />
                            <flux:error name="startTime" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="font-bold text-stone-750 dark:text-stone-300">Waktu Selesai</flux:label>
                            <flux:input type="time" wire:model="endTime" class="bg-white dark:bg-stone-950" />
                            <flux:error name="endTime" />
                        </flux:field>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label class="font-bold text-stone-750 dark:text-stone-300">Kuota Penerimaan</flux:label>
                            <flux:input type="number" wire:model="quota" min="1" class="bg-white dark:bg-stone-950" />
                            <flux:error name="quota" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="font-bold text-stone-750 dark:text-stone-300">Status</flux:label>
                            <div class="mt-2.5 flex items-center gap-2">
                                <flux:checkbox wire:model="isActive" label="Aktif" class="text-accent" />
                            </div>
                            <flux:error name="isActive" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label class="font-bold text-stone-750 dark:text-stone-300">Batas Akhir Pemesanan (Deadline)</flux:label>
                        <flux:input type="datetime-local" wire:model="orderDeadline" class="bg-white dark:bg-stone-950" />
                        <flux:error name="orderDeadline" />
                    </flux:field>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-stone-100 dark:border-stone-900">
                        @if ($editingSlotId)
                            <flux:button type="button" wire:click="resetInputFields" variant="ghost" class="font-bold rounded-xl">Batal</flux:button>
                        @endif
                        <flux:button type="submit" variant="primary" class="font-extrabold rounded-xl transition-transform duration-300 hover:scale-[1.02] active:scale-[0.98]">
                            {{ $editingSlotId ? 'Simpan Perubahan' : 'Tambah Slot' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
