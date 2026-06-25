<div class="max-w-3xl mx-auto space-y-8">
    @if ($order && $order->payment_status->value === 'pending')
        <!-- Load Midtrans Snap JS -->
        @if ($isProduction)
            <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
        @else
            <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
        @endif
    @endif

    <div class="flex items-center gap-2 text-sm text-stone-500 dark:text-stone-400">
        <a href="{{ route('home') }}" class="hover:text-accent dark:hover:text-white font-medium" wire:navigate>Katalog</a>
        <flux:icon icon="chevron-right" class="h-3 w-3" />
        <span class="text-stone-400 dark:text-stone-500 font-semibold">Lacak Pesanan</span>
    </div>

    <div class="flex items-center justify-between">
        <flux:heading size="xl" class="font-black text-stone-950 dark:text-white tracking-tight">Lacak Status Pesanan</flux:heading>
    </div>

    <!-- Search Form Card -->
    <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-6 shadow-sm space-y-4">
        <form wire:submit.prevent="track" class="grid grid-cols-1 sm:grid-cols-5 gap-4 items-end">
            <div class="sm:col-span-2">
                <flux:field>
                    <flux:label class="font-bold text-stone-750 dark:text-stone-300">Kode Pesanan</flux:label>
                    <flux:input wire:model="searchCode" placeholder="Contoh: UMK-20260625-XXXXXX" class="bg-white dark:bg-stone-950" />
                </flux:field>
            </div>
            <div class="sm:col-span-2">
                <flux:field>
                    <flux:label class="font-bold text-stone-750 dark:text-stone-300">Nomor WhatsApp</flux:label>
                    <flux:input wire:model="searchPhone" placeholder="Contoh: 08123456789" class="bg-white dark:bg-stone-950" />
                </flux:field>
            </div>
            <div class="sm:col-span-1">
                <flux:button type="submit" variant="primary" class="w-full font-extrabold py-2.5 shadow-sm transition-transform duration-300 hover:scale-[1.02] active:scale-[0.98] min-h-[44px]">
                    Cari
                </flux:button>
            </div>
        </form>

        @if ($errorMessage)
            <div class="p-4 bg-red-50 dark:bg-red-950/20 text-red-650 dark:text-red-400 rounded-2xl text-sm font-bold flex items-center gap-2">
                <flux:icon icon="exclamation-circle" class="h-5 w-5 text-red-500 shrink-0" />
                <span>{{ $errorMessage }}</span>
            </div>
        @endif
    </div>

    @if ($order)
        <!-- Order Progress Timeline Card -->
        <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-6 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pb-4 border-b border-stone-100 dark:border-stone-900">
                <div>
                    <span class="text-xs text-stone-500 dark:text-stone-400 font-extrabold uppercase tracking-wider">Pelanggan</span>
                    <flux:heading size="lg" class="font-black text-stone-950 dark:text-white tracking-tight">{{ $order->customer_name }}</flux:heading>
                    <p class="text-xs text-stone-500 dark:text-stone-400 mt-1 font-semibold">Kode: {{ $order->order_code }}</p>
                </div>
                <div class="text-right sm:text-right">
                    <span class="text-xs text-stone-500 dark:text-stone-400 font-extrabold uppercase tracking-wider">Metode</span>
                    <div class="font-extrabold text-stone-950 dark:text-white">
                        @if ($order->fulfillment_type->value === 'pickup')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-stone-100 dark:bg-stone-900 text-xs font-black text-stone-800 dark:text-stone-200">
                                <flux:icon icon="home-modern" class="h-3.5 w-3.5" /> Ambil Sendiri
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-accent/10 text-xs font-black text-accent">
                                <flux:icon icon="truck" class="h-3.5 w-3.5" /> Kirim Alamat
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cancellation State Alert -->
            @if ($order->order_status->value === 'cancelled')
                <div class="p-4 bg-red-50 dark:bg-red-950/20 text-red-650 dark:text-red-400 rounded-2xl text-sm font-bold space-y-1">
                    <div class="flex items-center gap-2">
                        <flux:icon icon="x-circle" class="h-5 w-5 text-red-500 shrink-0" />
                        <span>Pesanan Dibatalkan</span>
                    </div>
                    @if ($order->cancellation_reason)
                        <p class="text-xs font-normal text-stone-600 dark:text-stone-400 pl-7">Alasan: {{ $order->cancellation_reason }}</p>
                    @endif
                </div>
            @endif

            <!-- Visual Order Status Timeline -->
            @if ($order->order_status->value !== 'cancelled')
                <div class="relative py-4">
                    <!-- Progress Bar Line -->
                    <div class="absolute left-6 sm:left-1/2 top-4 bottom-4 w-1 -ml-0.5 bg-stone-150 dark:bg-stone-900 z-0"></div>

                    @php
                        $statuses = [
                            'awaiting_payment' => ['title' => 'Menunggu Pembayaran', 'desc' => 'Selesaikan pembayaran Anda.', 'icon' => 'credit-card'],
                            'confirmed' => ['title' => 'Pesanan Dikonfirmasi', 'desc' => 'Pembayaran berhasil dikonfirmasi.', 'icon' => 'check-circle'],
                            'processing' => ['title' => 'Sedang Disiapkan', 'desc' => 'Pesanan sedang diracik/disiapkan.', 'icon' => 'arrow-path'],
                            $order->fulfillment_type->value === 'pickup' ? 'ready_for_pickup' : 'out_for_delivery' => [
                                'title' => $order->fulfillment_type->value === 'pickup' ? 'Siap Diambil' : 'Dalam Pengiriman',
                                'desc' => $order->fulfillment_type->value === 'pickup' ? 'Silakan ambil di toko.' : 'Pesanan sedang diantar kurir.',
                                'icon' => $order->fulfillment_type->value === 'pickup' ? 'shopping-bag' : 'truck'
                            ],
                            'completed' => ['title' => 'Selesai', 'desc' => 'Pesanan telah diterima.', 'icon' => 'sparkles'],
                        ];

                        $currentIndex = 0;
                        $keys = array_keys($statuses);
                        $activeKey = $order->order_status->value;

                        // Map actual status ready_for_pickup/out_for_delivery back if they are active
                        if (in_array($activeKey, ['ready_for_pickup', 'out_for_delivery'])) {
                            $activeKey = $order->fulfillment_type->value === 'pickup' ? 'ready_for_pickup' : 'out_for_delivery';
                        }

                        foreach ($keys as $idx => $k) {
                            if ($k === $activeKey) {
                                $currentIndex = $idx;
                                break;
                            }
                        }
                    @endphp

                    <div class="space-y-8 relative z-10">
                        @foreach ($statuses as $statusKey => $details)
                            @php
                                $statusIdx = array_search($statusKey, $keys);
                                $isCompleted = $statusIdx < $currentIndex;
                                $isActive = $statusIdx === $currentIndex;
                                $isUpcoming = $statusIdx > $currentIndex;

                                $circleClass = 'bg-stone-200 text-stone-500 border-stone-300 dark:bg-stone-900 dark:text-stone-600 dark:border-stone-850';
                                if ($isCompleted) {
                                    $circleClass = 'bg-accent text-white border-accent';
                                } elseif ($isActive) {
                                    $circleClass = 'bg-white dark:bg-stone-950 text-accent border-2 border-accent ring-4 ring-accent/15';
                                }
                            @endphp

                            <div class="flex flex-row sm:flex-row items-start gap-4 sm:gap-0">
                                <!-- Marker -->
                                <div class="flex items-center justify-center sm:w-1/2 sm:justify-end sm:pr-8">
                                    <div class="flex items-center gap-3">
                                        <div class="hidden sm:block text-right">
                                            <h4 class="text-sm font-black {{ $isActive ? 'text-accent' : ($isCompleted ? 'text-stone-900 dark:text-stone-100' : 'text-stone-400 dark:text-stone-600') }}">
                                                {{ $details['title'] }}
                                            </h4>
                                            <p class="text-xs text-stone-500 dark:text-stone-400 font-semibold">{{ $details['desc'] }}</p>
                                        </div>
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center border font-black {{ $circleClass }} shrink-0">
                                            <flux:icon icon="{{ $details['icon'] }}" class="h-5 w-5" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Text for Mobile -->
                                <div class="sm:hidden flex-1 pt-1.5">
                                    <h4 class="text-sm font-black {{ $isActive ? 'text-accent' : ($isCompleted ? 'text-stone-900 dark:text-stone-100' : 'text-stone-400 dark:text-stone-600') }}">
                                        {{ $details['title'] }}
                                    </h4>
                                    <p class="text-xs text-stone-500 dark:text-stone-400 font-semibold">{{ $details['desc'] }}</p>
                                </div>

                                <!-- Empty space for grid on Desktop -->
                                <div class="hidden sm:block sm:w-1/2 sm:pl-8"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Payment CTA Card (Only if Pending) -->
        @if ($order->payment_status->value === 'pending' && $order->payment && $order->payment->snap_token)
            <div class="rounded-3xl border border-accent/20 bg-accent/5 dark:bg-accent/10 p-6 shadow-sm space-y-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="space-y-1 text-center sm:text-left">
                    <flux:heading size="lg" class="font-black text-accent tracking-tight">Selesaikan Pembayaran Anda</flux:heading>
                    <p class="text-sm text-stone-605 dark:text-stone-400 font-medium">Batas waktu pembayaran sebelum pesanan dibatalkan otomatis: <br><strong class="text-stone-900 dark:text-white font-black">{{ $order->payment_expires_at->format('d M Y, H:i') }} WIB</strong></p>
                </div>
                
                <div class="shrink-0 w-full sm:w-auto" x-data="{
                    pay() {
                        if (window.snap) {
                            window.snap.pay('{{ $order->payment->snap_token }}', {
                                onSuccess: function(result) {
                                    $wire.call('refreshOrder');
                                },
                                onPending: function(result) {
                                    $wire.call('refreshOrder');
                                },
                                onError: function(result) {
                                    $wire.call('refreshOrder');
                                },
                                onClose: function() {
                                    $wire.call('refreshOrder');
                                }
                            });
                        }
                    }
                }">
                    <flux:button @click="pay" variant="primary" class="w-full font-black py-3 px-6 shadow-sm transition-transform duration-300 hover:scale-[1.02] active:scale-[0.98] min-h-[44px]">
                        <flux:icon icon="credit-card" class="mr-2 h-5 w-5" /> Bayar Sekarang
                    </flux:button>
                </div>
            </div>
        @endif

        <!-- Order Items & Billing Summary -->
        <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-6 shadow-sm space-y-6">
            <flux:heading size="lg" class="font-black text-stone-950 dark:text-white tracking-tight">Rincian Belanja</flux:heading>

            <div class="divide-y divide-stone-100 dark:divide-stone-900 space-y-4">
                @foreach ($order->items as $item)
                    <div class="flex justify-between items-start gap-4 pt-4 first:pt-0">
                        <div>
                            <span class="text-sm font-extrabold text-stone-900 dark:text-white">
                                {{ $item->product_name }}
                            </span>
                            <div class="text-xs text-stone-500 dark:text-stone-400 flex flex-wrap gap-1 mt-1 font-semibold uppercase tracking-wider">
                                <span>Varian: {{ $item->variant_name }}</span>
                                <span>× {{ $item->quantity }}</span>
                            </div>

                            @if ($item->options->isNotEmpty())
                                <div class="text-xs text-stone-500 dark:text-stone-400 mt-1 pl-2 border-l border-stone-200 dark:border-stone-800 space-y-0.5">
                                    @foreach ($item->options as $opt)
                                        <div>- {{ $opt->group_name }}: {{ $opt->value_name }}</div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($item->addons->isNotEmpty())
                                <div class="text-xs text-stone-500 dark:text-stone-400 mt-1 pl-2 border-l border-stone-200 dark:border-stone-800 space-y-0.5">
                                    @foreach ($item->addons as $addon)
                                        <div>+ {{ $addon->addon_name }} (Rp{{ number_format($addon->unit_price, 0, ',', '.') }})</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <span class="font-mono text-sm font-black text-stone-900 dark:text-white shrink-0">
                            Rp{{ number_format($item->line_total, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- Billing Breakdown -->
            <div class="border-t border-stone-100 dark:border-stone-900 pt-4 space-y-3 text-sm">
                <div class="flex justify-between text-stone-600 dark:text-stone-400 font-medium">
                    <span>Subtotal Belanja</span>
                    <span class="font-mono font-bold text-stone-900 dark:text-white">
                        Rp{{ number_format($order->subtotal, 0, ',', '.') }}
                    </span>
                </div>

                @if ($order->fulfillment_type->value === 'delivery')
                    <div class="flex justify-between text-stone-600 dark:text-stone-400 font-medium">
                        <span>Ongkos Kirim</span>
                        <span class="font-mono font-bold text-stone-900 dark:text-white">
                            Rp{{ number_format($order->delivery_fee, 0, ',', '.') }}
                        </span>
                    </div>
                @endif

                <div class="border-t border-stone-100 dark:border-stone-900 pt-4 flex justify-between items-center">
                    <span class="font-extrabold text-stone-900 dark:text-white">Total Tagihan</span>
                    <span class="font-mono text-xl font-black text-accent">
                        Rp{{ number_format($order->grand_total, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Schedule Slot & Delivery Address Card -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-6 shadow-sm space-y-3">
                <flux:heading size="sm" class="font-black text-stone-500 dark:text-stone-400 uppercase tracking-wider text-xs">Jadwal Pengambilan / Pengiriman</flux:heading>
                <div class="flex items-start gap-3">
                    <flux:icon icon="calendar" class="h-5 w-5 text-accent shrink-0 mt-0.5" />
                    <div>
                        <p class="font-extrabold text-stone-900 dark:text-white text-sm">
                            {{ $order->scheduled_at->format('d M Y') }}
                        </p>
                        @if ($order->scheduleSlot)
                            <p class="text-xs text-stone-500 dark:text-stone-400 font-medium">
                                Jam {{ $order->scheduleSlot->timeRange }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            @if ($order->fulfillment_type->value === 'delivery' && $order->delivery)
                <div class="rounded-3xl border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-950 p-6 shadow-sm space-y-3">
                    <flux:heading size="sm" class="font-black text-stone-500 dark:text-stone-400 uppercase tracking-wider text-xs">Alamat Pengiriman</flux:heading>
                    <div class="flex items-start gap-3">
                        <flux:icon icon="map-pin" class="h-5 w-5 text-accent shrink-0 mt-0.5" />
                        <div>
                            <p class="font-extrabold text-stone-900 dark:text-white text-sm">
                                {{ $order->delivery->recipient_name }} ({{ $order->delivery->recipient_phone }})
                            </p>
                            <p class="text-xs text-stone-500 dark:text-stone-400 font-medium mt-1">
                                {{ $order->delivery->address }}
                            </p>
                            @if ($order->delivery->address_note)
                                <p class="text-xs text-stone-450 dark:text-stone-500 italic mt-0.5">
                                    Catatan: {{ $order->delivery->address_note }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
