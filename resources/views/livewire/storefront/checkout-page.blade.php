<div class="space-y-6">
    <!-- Leaflet Resources -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <div class="flex items-center gap-2 text-sm text-stone-500 dark:text-stone-400">
        <a href="{{ route('home') }}" class="hover:text-accent dark:hover:text-white font-medium" wire:navigate>Katalog</a>
        <flux:icon icon="chevron-right" class="h-3 w-3" />
        <a href="{{ route('home.cart') }}" class="hover:text-accent dark:hover:text-white font-medium" wire:navigate>Keranjang</a>
        <flux:icon icon="chevron-right" class="h-3 w-3" />
        <span class="text-stone-400 dark:text-stone-500 font-semibold">Checkout</span>
    </div>

    <div class="flex items-center justify-between">
        <flux:heading size="xl" class="font-black text-stone-950 dark:text-white tracking-tight">Checkout Pemesanan</flux:heading>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Form Checkout (Left Columns) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 1. Identitas Pelanggan -->
            <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-stone-100 dark:border-stone-900">
                    <div class="h-8 w-8 rounded-lg bg-accent/10 flex items-center justify-center text-accent font-black">1</div>
                    <flux:heading size="lg" class="font-black text-stone-950 dark:text-white">Identitas Pelanggan</flux:heading>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label class="font-bold text-stone-750 dark:text-stone-300">Nama Lengkap</flux:label>
                        <flux:input wire:model="customerName" placeholder="Masukkan nama penerima..." class="bg-white dark:bg-stone-950" />
                        <flux:error name="customerName" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="font-bold text-stone-750 dark:text-stone-300">Nomor WhatsApp</flux:label>
                        <flux:input wire:model="customerWhatsapp" placeholder="Contoh: 08123456789..." class="bg-white dark:bg-stone-950" />
                        <flux:error name="customerWhatsapp" />
                    </flux:field>
                </div>
            </div>

            <!-- 2. Metode Pemenuhan -->
            <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-stone-100 dark:border-stone-900">
                    <div class="h-8 w-8 rounded-lg bg-accent/10 flex items-center justify-center text-accent font-black">2</div>
                    <flux:heading size="lg" class="font-black text-stone-950 dark:text-white">Metode Pemenuhan Pesanan</flux:heading>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Pickup option card -->
                    <label class="flex flex-col items-center justify-center p-5 border-2 rounded-2xl cursor-pointer transition-all duration-300 ease-out-quint gap-2 relative shadow-2xs select-none {{ $fulfillmentMethod === 'pickup' ? 'border-accent bg-accent/5 dark:bg-accent/10' : 'border-stone-200 dark:border-stone-850 hover:bg-stone-50 dark:hover:bg-stone-900/40 hover:border-stone-300' }} focus-within:ring-2 focus-within:ring-accent focus-within:ring-offset-2 dark:focus-within:ring-offset-stone-950">
                        <input type="radio" wire:model.live="fulfillmentMethod" value="pickup" class="sr-only" />
                        <flux:icon icon="home-modern" class="h-6 w-6 text-stone-600 dark:text-stone-400 {{ $fulfillmentMethod === 'pickup' ? '!text-accent dark:!text-accent' : '' }}" />
                        <span class="font-extrabold text-sm {{ $fulfillmentMethod === 'pickup' ? 'text-accent' : 'text-stone-700 dark:text-stone-300' }}">Ambil Sendiri</span>
                    </label>

                    <!-- Delivery option card -->
                    <label class="flex flex-col items-center justify-center p-5 border-2 rounded-2xl cursor-pointer transition-all duration-300 ease-out-quint gap-2 relative shadow-2xs select-none {{ $fulfillmentMethod === 'delivery' ? 'border-accent bg-accent/5 dark:bg-accent/10' : 'border-stone-200 dark:border-stone-850 hover:bg-stone-50 dark:hover:bg-stone-900/40 hover:border-stone-300' }} focus-within:ring-2 focus-within:ring-accent focus-within:ring-offset-2 dark:focus-within:ring-offset-stone-950">
                        <input type="radio" wire:model.live="fulfillmentMethod" value="delivery" class="sr-only" />
                        <flux:icon icon="truck" class="h-6 w-6 text-stone-600 dark:text-stone-400 {{ $fulfillmentMethod === 'delivery' ? '!text-accent dark:!text-accent' : '' }}" />
                        <span class="font-extrabold text-sm {{ $fulfillmentMethod === 'delivery' ? 'text-accent' : 'text-stone-700 dark:text-stone-300' }}">Kirim Ke Alamat</span>
                    </label>
                </div>
                <flux:error name="fulfillmentMethod" />
            </div>

            <!-- 3. Jadwal Operasional -->
            <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-stone-100 dark:border-stone-900">
                    <div class="h-8 w-8 rounded-lg bg-accent/10 flex items-center justify-center text-accent font-black">3</div>
                    <flux:heading size="lg" class="font-black text-stone-950 dark:text-white">Jadwal Pengambilan / Pengiriman</flux:heading>
                </div>

                <flux:field>
                    <flux:label class="font-bold text-stone-750 dark:text-stone-300">Pilih Slot Waktu</flux:label>
                    <flux:select wire:model="scheduleSlotId" placeholder="Pilih tanggal & waktu..." class="bg-white dark:bg-stone-950">
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
            <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-6 shadow-sm space-y-4 {{ $fulfillmentMethod === 'delivery' ? 'block' : 'hidden' }}">
                <div class="flex items-center gap-3 pb-3 border-b border-stone-100 dark:border-stone-900">
                    <div class="h-8 w-8 rounded-lg bg-accent/10 flex items-center justify-center text-accent font-black">4</div>
                    <flux:heading size="lg" class="font-black text-stone-950 dark:text-white">Alamat & Titik Pengiriman</flux:heading>
                </div>

                <div class="space-y-4">
                    <flux:field>
                        <flux:label class="font-bold text-stone-750 dark:text-stone-300">Alamat Lengkap Penerima</flux:label>
                        <flux:textarea wire:model="deliveryAddress" placeholder="Masukkan alamat lengkap penerima..." rows="3" class="bg-white dark:bg-stone-950" />
                        <flux:error name="deliveryAddress" />
                    </flux:field>

                    <div class="space-y-2.5">
                        <flux:label class="font-bold text-stone-750 dark:text-stone-300">Titik Lokasi Peta</flux:label>
                        <flux:text class="text-xs text-stone-500">Geser pin biru atau klik peta di titik alamat pengiriman Anda.</flux:text>
                        
                        <!-- Map Container -->
                        <div wire:ignore class="relative w-full rounded-2xl overflow-hidden border border-stone-200 dark:border-stone-800 shadow-inner z-10">
                            <div id="leaflet-map" class="h-72 w-full"></div>
                        </div>

                        <!-- Coordinate Display and Distance Validation -->
                        <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-850 space-y-2">
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center text-sm gap-2">
                                <span class="text-stone-500 dark:text-stone-400 font-medium">Estimasi Jarak dari Toko:</span>
                                <span class="font-mono font-black text-stone-900 dark:text-white">
                                    {{ number_format($distanceMeters / 1000.0, 2, ',', '.') }} km
                                </span>
                            </div>
                            
                            @if ($distanceMeters > $store->max_delivery_distance_meters)
                                <div class="p-3.5 bg-red-50 dark:bg-red-950/20 text-red-650 dark:text-red-400 rounded-2xl text-xs flex items-center gap-2 font-bold">
                                    <flux:icon icon="exclamation-circle" class="h-4.5 w-4.5 shrink-0 text-red-500" />
                                    <span>Jarak pengiriman melebihi radius maksimal 10 km.</span>
                                </div>
                            @elseif ($distanceMeters > 0)
                                <div class="p-3.5 bg-green-50 dark:bg-green-950/20 text-green-650 dark:text-green-400 rounded-2xl text-xs flex items-center gap-2 font-bold">
                                    <flux:icon icon="check-circle" class="h-4.5 w-4.5 shrink-0 text-green-500" />
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
            <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-6 shadow-sm space-y-4">
                <flux:field>
                    <flux:label class="font-bold text-stone-750 dark:text-stone-300">Catatan Pesanan (Opsional)</flux:label>
                    <flux:textarea wire:model="notes" placeholder="Catatan tambahan seperti detail patokan alamat, dll..." rows="2" class="bg-white dark:bg-stone-950" />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </div>

        <!-- Order Summary (Right Column) -->
        <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-24">
            <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-6 shadow-sm space-y-6">
                <flux:heading size="lg" class="font-black text-stone-950 dark:text-white tracking-tight">Ringkasan Pesanan</flux:heading>

                <!-- List Items -->
                <div class="divide-y divide-stone-100 dark:divide-stone-900 space-y-4">
                    @foreach ($cartItems as $item)
                        <div class="flex justify-between items-start gap-4 pt-4 first:pt-0">
                            <div>
                                <span class="text-sm font-extrabold text-stone-900 dark:text-white">
                                    {{ $item['product']->name }}
                                </span>
                                <div class="text-xs text-stone-500 dark:text-stone-400 flex flex-wrap gap-1 mt-1 font-semibold uppercase tracking-wider">
                                    <span>Varian: {{ $item['variant']->name }}</span>
                                    <span>× {{ $item['quantity'] }}</span>
                                </div>
                            </div>
                            <span class="font-mono text-sm font-black text-stone-900 dark:text-white shrink-0">
                                Rp{{ number_format($item['total_price'], 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <!-- Price Details -->
                <div class="border-t border-stone-100 dark:border-stone-900 pt-4 space-y-3 text-sm">
                    <div class="flex justify-between text-stone-600 dark:text-stone-400 font-medium">
                        <span>Subtotal Belanja</span>
                        <span class="font-mono font-bold text-stone-900 dark:text-white">
                            Rp{{ number_format($subtotal, 0, ',', '.') }}
                        </span>
                    </div>

                    @if ($fulfillmentMethod === 'delivery')
                        <div class="flex justify-between text-stone-600 dark:text-stone-400 font-medium">
                            <span>Ongkos Kirim</span>
                            <span class="font-mono font-bold text-stone-900 dark:text-white">
                                Rp{{ number_format($deliveryFee, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    <div class="border-t border-stone-100 dark:border-stone-900 pt-4 flex justify-between items-center">
                        <span class="font-extrabold text-stone-900 dark:text-white">Total Tagihan</span>
                        <span class="font-mono text-xl font-black text-accent">
                            Rp{{ number_format($total, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- CTA Submit Button -->
                <flux:button 
                    wire:click="submit" 
                    variant="primary" 
                    class="w-full font-extrabold py-3 shadow-sm transition-transform duration-300 hover:scale-[1.02] active:scale-[0.98] min-h-[44px]"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Konfirmasi Pemesanan</span>
                    <span wire:loading class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </flux:button>
            </div>
        </div>
    </div>
</div>
