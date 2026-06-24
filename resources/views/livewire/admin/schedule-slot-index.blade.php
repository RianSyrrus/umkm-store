<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Kelola Slot Jadwal</flux:heading>
            <flux:text>Atur slot waktu pengiriman dan pengambilan pre-order toko Anda.</flux:text>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- List Slot Jadwal -->
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center justify-between mb-4 gap-4">
                    <div class="flex-1 max-w-sm">
                        <flux:input 
                            wire:model.live="search" 
                            placeholder="Cari tanggal (YYYY-MM-DD)..." 
                            icon="magnifying-glass" 
                            clearable 
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
                                <flux:table.cell class="font-medium text-zinc-900 dark:text-white">
                                    {{ $slot->date->format('d M Y') }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    {{ $slot->timeRange }}
                                </flux:table.cell>
                                <flux:table.cell class="text-zinc-600 dark:text-zinc-400 text-sm">
                                    {{ $slot->order_deadline->format('d M Y, H:i') }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <span class="font-semibold text-zinc-900 dark:text-white">{{ $slot->reserved_count }}</span>
                                    <span class="text-zinc-400">/</span>
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ $slot->quota }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($slot->isAvailable())
                                        <flux:badge color="green" size="sm">Tersedia</flux:badge>
                                    @elseif ($slot->is_active && $slot->reserved_count >= $slot->quota)
                                        <flux:badge color="amber" size="sm">Penuh</flux:badge>
                                    @elseif ($slot->is_active && now()->gt($slot->order_deadline))
                                        <flux:badge color="orange" size="sm">Lewat Batas</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">Nonaktif</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button 
                                            wire:click="editSlot({{ $slot->id }})" 
                                            icon="pencil" 
                                            variant="ghost" 
                                            size="sm" 
                                            title="Ubah Slot" 
                                        />
                                        <flux:button 
                                            wire:click="deleteSlot({{ $slot->id }})" 
                                            wire:confirm="Apakah Anda yakin ingin menghapus slot jadwal ini?"
                                            icon="trash" 
                                            variant="ghost" 
                                            size="sm" 
                                            color="red" 
                                            title="Hapus Slot" 
                                        />
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6" class="text-center py-8 text-zinc-400">
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
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ $editingSlotId ? 'Ubah Slot Jadwal' : 'Tambah Slot Baru' }}
                    </flux:heading>
                    <flux:text>
                        {{ $editingSlotId ? 'Perbarui informasi slot jadwal operasional terpilih.' : 'Buat slot jadwal operasional baru.' }}
                    </flux:text>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <flux:field>
                        <flux:label>Tanggal Slot</flux:label>
                        <flux:input type="date" wire:model="date" />
                        <flux:error name="date" />
                    </flux:field>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Waktu Mulai</flux:label>
                            <flux:input type="time" wire:model="startTime" />
                            <flux:error name="startTime" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Waktu Selesai</flux:label>
                            <flux:input type="time" wire:model="endTime" />
                            <flux:error name="endTime" />
                        </flux:field>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Kuota Penerimaan</flux:label>
                            <flux:input type="number" wire:model="quota" min="1" />
                            <flux:error name="quota" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Status</flux:label>
                            <div class="mt-2 flex items-center gap-2">
                                <flux:checkbox wire:model="isActive" label="Aktif" />
                            </div>
                            <flux:error name="isActive" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Batas Akhir Pemesanan (Deadline)</flux:label>
                        <flux:input type="datetime-local" wire:model="orderDeadline" />
                        <flux:error name="orderDeadline" />
                    </flux:field>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                        @if ($editingSlotId)
                            <flux:button type="button" wire:click="resetInputFields" variant="ghost">Batal</flux:button>
                        @endif
                        <flux:button type="submit" variant="primary">
                            {{ $editingSlotId ? 'Simpan Perubahan' : 'Tambah Slot' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
