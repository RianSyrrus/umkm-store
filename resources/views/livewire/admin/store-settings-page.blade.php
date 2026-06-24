<div class="space-y-6">
    <flux:heading size="xl">Pengaturan Toko</flux:heading>
    <flux:text>Kelola profil, kontak, dan biaya operasional toko Anda.</flux:text>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Profile & Contact -->
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <form wire:submit="save" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <flux:field>
                            <flux:label>Nama Toko</flux:label>
                            <flux:input wire:model="name" placeholder="Masukkan nama toko" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>WhatsApp (Toko)</flux:label>
                            <flux:input wire:model="whatsapp" placeholder="Contoh: 08123456789" />
                            <flux:error name="whatsapp" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Alamat Toko</flux:label>
                        <flux:textarea wire:model="address" placeholder="Tulis alamat lengkap toko" rows="3" />
                        <flux:error name="address" />
                    </flux:field>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <flux:field>
                            <flux:label>Latitude</flux:label>
                            <flux:input type="number" step="any" wire:model="latitude" placeholder="Contoh: -6.200000" />
                            <flux:error name="latitude" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Longitude</flux:label>
                            <flux:input type="number" step="any" wire:model="longitude" placeholder="Contoh: 106.816667" />
                            <flux:error name="longitude" />
                        </flux:field>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <flux:field>
                            <flux:label>Tarif Dasar Pengiriman (Rp)</flux:label>
                            <flux:input type="number" wire:model="baseDeliveryFee" placeholder="Tarif dasar" />
                            <flux:error name="baseDeliveryFee" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Tarif per KM (Rp)</flux:label>
                            <flux:input type="number" wire:model="deliveryFeePerKm" placeholder="Tarif per km" />
                            <flux:error name="deliveryFeePerKm" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Batas Stok Menipis</flux:label>
                            <flux:input type="number" wire:model="lowStockThreshold" placeholder="Batas stok" />
                            <flux:error name="lowStockThreshold" />
                        </flux:field>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-150 dark:border-zinc-700">
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                            Simpan Perubahan
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info / Sidebar -->
        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">Radius Pengiriman</flux:heading>
                <flux:text class="mt-2 block">
                    Batas radius pengiriman lokal disetel secara otomatis maksimal **10 kilometer** dari koordinat toko. Pesanan di luar radius ini akan ditolak secara otomatis oleh sistem saat checkout.
                </flux:text>
            </div>
        </div>
    </div>
</div>
