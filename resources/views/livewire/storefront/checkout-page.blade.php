<div class="space-y-6">
    <!-- Leaflet Resources -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
        <a href="{{ route('home') }}" class="hover:text-zinc-800 dark:hover:text-white" wire:navigate>Katalog</a>
        <flux:icon icon="chevron-right" class="h-3 w-3" />
        <a href="{{ route('home.cart') }}" class="hover:text-zinc-800 dark:hover:text-white" wire:navigate>Keranjang</a>
        <flux:icon icon="chevron-right" class="h-3 w-3" />
        <span class="text-zinc-400">Checkout</span>
    </div>

    <div class="flex items-center justify-between">
        <flux:heading size="xl">Checkout Pemesanan</flux:heading>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Form Checkout (Left Columns) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 1. Identitas Pelanggan -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-850">
                    <div class="h-8 w-8 rounded-lg bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">1</div>
                    <flux:heading size="lg">Identitas Pelanggan</flux:heading>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Nama Lengkap</flux:label>
                        <flux:input wire:model="customerName" placeholder="Masukkan nama penerima..." />
                        <flux:error name="customerName" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nomor WhatsApp</flux:label>
                        <flux:input wire:model="customerWhatsapp" placeholder="Contoh: 08123456789..." />
                        <flux:error name="customerWhatsapp" />
                    </flux:field>
                </div>
            </div>

            <!-- 2. Metode Pemenuhan -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-850">
                    <div class="h-8 w-8 rounded-lg bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">2</div>
                    <flux:heading size="lg">Metode Pemenuhan Pesanan</flux:heading>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Pickup option card -->
                    <label class="flex flex-col items-center justify-center p-4 border rounded-2xl cursor-pointer transition-all gap-2 relative {{ $fulfillmentMethod === 'pickup' ? 'border-indigo-600 bg-indigo-50/50 dark:border-indigo-400 dark:bg-indigo-950/20' : 'border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-900' }}">
                        <input type="radio" wire:model.live="fulfillmentMethod" value="pickup" class="sr-only" />
                        <flux:icon icon="home-modern" class="h-6 w-6 text-zinc-600 dark:text-zinc-400 {{ $fulfillmentMethod === 'pickup' ? '!text-indigo-600 dark:!text-indigo-400' : '' }}" />
                        <span class="font-semibold text-sm {{ $fulfillmentMethod === 'pickup' ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-700 dark:text-zinc-300' }}">Ambil Sendiri</span>
                    </label>

                    <!-- Delivery option card -->
                    <label class="flex flex-col items-center justify-center p-4 border rounded-2xl cursor-pointer transition-all gap-2 relative {{ $fulfillmentMethod === 'delivery' ? 'border-indigo-600 bg-indigo-50/50 dark:border-indigo-400 dark:bg-indigo-950/20' : 'border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-900' }}">
                        <input type="radio" wire:model.live="fulfillmentMethod" value="delivery" class="sr-only" />
                        <flux:icon icon="truck" class="h-6 w-6 text-zinc-600 dark:text-zinc-400 {{ $fulfillmentMethod === 'delivery' ? '!text-indigo-600 dark:!text-indigo-400' : '' }}" />
                        <span class="font-semibold text-sm {{ $fulfillmentMethod === 'delivery' ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-700 dark:text-zinc-300' }}">Kirim Ke Alamat</span>
                    </label>
                </div>
                <flux:error name="fulfillmentMethod" />
            </div>

            <!-- 3. Jadwal Operasional -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-850">
                    <div class="h-8 w-8 rounded-lg bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">3</div>
                    <flux:heading size="lg">Jadwal Pengambilan / Pengiriman</flux:heading>
                </div>

                <flux:field>
                    <flux:label>Pilih Slot Waktu</flux:label>
                    <flux:select wire:model="scheduleSlotId" placeholder="Pilih tanggal & waktu...">
                        @foreach ($availableSlots as $slot)
                            <option value="{{ $slot->id }}">
                                {{ $slot->date->format('d M Y') }} ({{ $slot->timeRange }})
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="scheduleSlotId" />
                </flux:field>
            </div>

            <!-- 4. Alamat & Peta (Hanya Kirim) -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-6 shadow-sm space-y-4 {{ $fulfillmentMethod === 'delivery' ? 'block' : 'hidden' }}">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-850">
                    <div class="h-8 w-8 rounded-lg bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">4</div>
                    <flux:heading size="lg">Alamat & Titik Pengiriman</flux:heading>
                </div>

                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Alamat Lengkap Penerima</flux:label>
                        <flux:textarea wire:model="deliveryAddress" placeholder="Masukkan alamat lengkap penerima..." rows="3" />
                        <flux:error name="deliveryAddress" />
                    </flux:field>

                    <div class="space-y-2">
                        <flux:label>Titik Lokasi Peta</flux:label>
                        <flux:text class="text-xs">Geser pin biru atau klik peta di titik alamat pengiriman Anda.</flux:text>
                        
                        <!-- Map Container -->
                        <div wire:ignore class="relative w-full rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 shadow-inner">
                            <div id="leaflet-map" class="h-72 w-full z-10"></div>
                        </div>

                        <!-- Coordinate Display and Distance Validation -->
                        <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 space-y-2">
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center text-sm gap-2">
                                <span class="text-zinc-500">Estimasi Jarak dari Toko:</span>
                                <span class="font-mono font-bold text-zinc-900 dark:text-white">
                                    {{ number_format($distanceMeters / 1000.0, 2, ',', '.') }} km
                                </span>
                            </div>
                            
                            @if ($distanceMeters > $store->max_delivery_distance_meters)
                                <div class="p-3 bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 rounded-xl text-xs flex items-center gap-2 font-medium">
                                    <flux:icon icon="exclamation-circle" class="h-4 w-4 shrink-0" />
                                    <span>Jarak pengiriman melebihi radius maksimal 10 km.</span>
                                </div>
                            @elseif ($distanceMeters > 0)
                                <div class="p-3 bg-green-50 dark:bg-green-950/20 text-green-600 dark:text-green-400 rounded-xl text-xs flex items-center gap-2 font-medium">
                                    <flux:icon icon="check-circle" class="h-4 w-4 shrink-0" />
                                    <span>Lokasi berada dalam jangkauan pengiriman.</span>
                                </div>
                            @endif

                            <flux:error name="distance" />
                        </div>
                    </div>
                </div>

                <!-- Alpine Map Logic Integration -->
                <div x-data="{
                    map: null,
                    marker: null,
                    initMap() {
                        if (this.map) {
                            setTimeout(() => { this.map.invalidateSize(); }, 200);
                            return;
                        }
                        const storeLat = {{ $store->latitude }};
                        const storeLng = {{ $store->longitude }};
                        const initialLat = @js($latitude) || storeLat;
                        const initialLng = @js($longitude) || storeLng;

                        this.map = L.map('leaflet-map').setView([initialLat, initialLng], 14);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(this.map);

                        // Store marker (Red)
                        const storeIcon = L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        });
                        L.marker([storeLat, storeLng], { icon: storeIcon })
                            .addTo(this.map)
                            .bindPopup('Toko UMKM')
                            .openPopup();

                        // User/Customer marker (Blue)
                        const userIcon = L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        });
                        this.marker = L.marker([initialLat, initialLng], {
                            draggable: true,
                            icon: userIcon
                        }).addTo(this.map);

                        // Events
                        this.marker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.$wire.call('setCoordinates', pos.lat, pos.lng);
                        });

                        this.map.on('click', (e) => {
                            this.marker.setLatLng(e.latlng);
                            this.$wire.call('setCoordinates', e.latlng.lat, e.latlng.lng);
                        });
                    }
                }" x-init="$watch('$wire.fulfillmentMethod', val => { if (val === 'delivery') { $nextTick(() => initMap()); } })">
                </div>
            </div>

            <!-- Notes -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-6 shadow-sm space-y-4">
                <flux:field>
                    <flux:label>Catatan Pesanan (Opsional)</flux:label>
                    <flux:textarea wire:model="notes" placeholder="Catatan tambahan seperti detail patokan alamat, dll..." rows="2" />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </div>

        <!-- Order Summary (Right Column) -->
        <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-24">
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-6 shadow-sm space-y-6">
                <flux:heading size="lg">Ringkasan Pesanan</flux:heading>

                <!-- List Items -->
                <div class="divide-y divide-zinc-100 dark:divide-zinc-850 space-y-4">
                    @foreach ($cartItems as $item)
                        <div class="flex justify-between items-start gap-4 pt-4 first:pt-0">
                            <div>
                                <span class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $item['product']->name }}
                                </span>
                                <div class="text-xs text-zinc-500 flex flex-wrap gap-1 mt-0.5">
                                    <span>Varian: {{ $item['variant']->name }}</span>
                                    <span>× {{ $item['quantity'] }}</span>
                                </div>
                            </div>
                            <span class="font-mono text-sm font-bold text-zinc-900 dark:text-white shrink-0">
                                Rp{{ number_format($item['total_price'], 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <!-- Price Details -->
                <div class="border-t border-zinc-100 dark:border-zinc-850 pt-4 space-y-2.5 text-sm">
                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                        <span>Subtotal Belanja</span>
                        <span class="font-mono font-medium text-zinc-900 dark:text-white">
                            Rp{{ number_format($subtotal, 0, ',', '.') }}
                        </span>
                    </div>

                    @if ($fulfillmentMethod === 'delivery')
                        <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                            <span>Ongkos Kirim</span>
                            <span class="font-mono font-medium text-zinc-900 dark:text-white">
                                Rp{{ number_format($deliveryFee, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    <div class="border-t border-zinc-100 dark:border-zinc-850 pt-3 flex justify-between items-center">
                        <span class="font-semibold text-zinc-900 dark:text-white">Total Tagihan</span>
                        <span class="font-mono text-lg font-extrabold text-zinc-950 dark:text-white">
                            Rp{{ number_format($total, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- CTA Submit Button -->
                <flux:button 
                    wire:click="submit" 
                    variant="primary" 
                    class="w-full"
                    wire:loading.attr="disabled"
                >
                    Konfirmasi Pemesanan
                </flux:button>
            </div>
        </div>
    </div>
</div>
