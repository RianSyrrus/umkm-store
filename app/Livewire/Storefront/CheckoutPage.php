<?php

namespace App\Livewire\Storefront;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\OutOfStockException;
use App\Exceptions\ScheduleSlotFullException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ScheduleSlot;
use App\Models\Store;
use App\Services\Cart\CartService;
use App\Services\Inventory\InventoryReservationService;
use App\Services\Maps\DeliveryFeeCalculator;
use App\Services\Maps\MapProvider;
use App\Services\Payments\PaymentGateway;
use App\ValueObjects\Coordinates;
use App\ValueObjects\NormalizedPhone;
use App\ValueObjects\OrderCode;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Checkout')]
class CheckoutPage extends Component
{
    public string $customerName = '';

    public string $customerWhatsapp = '';

    public string $fulfillmentMethod = 'pickup';

    public string $scheduleSlotId = '';

    public string $deliveryAddress = '';

    public ?float $latitude = null;

    public ?float $longitude = null;

    public int $distanceMeters = 0;

    public int $deliveryFee = 0;

    public string $notes = '';

    public int $subtotal = 0;

    public int $total = 0;

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $rules = [
            'customerName' => ['required', 'string', 'max:100'],
            'customerWhatsapp' => ['required', 'string', 'max:20'],
            'scheduleSlotId' => ['required', 'exists:schedule_slots,id'],
            'fulfillmentMethod' => ['required', 'in:pickup,delivery'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->fulfillmentMethod === 'delivery') {
            $rules['deliveryAddress'] = ['required', 'string', 'max:1000'];
            $rules['latitude'] = ['required', 'numeric', 'between:-90,90'];
            $rules['longitude'] = ['required', 'numeric', 'between:-180,180'];
        }

        return $rules;
    }

    /** @var array<string, string> */
    protected array $validationAttributes = [
        'customerName' => 'Nama Penerima',
        'customerWhatsapp' => 'Nomor WhatsApp',
        'scheduleSlotId' => 'Slot Jadwal',
        'fulfillmentMethod' => 'Metode Pemenuhan',
        'deliveryAddress' => 'Alamat Pengiriman',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'notes' => 'Catatan',
    ];

    public function mount(): void
    {
        $cartService = new CartService;
        $this->subtotal = $cartService->getTotalPrice();
        $this->total = $this->subtotal;

        $store = Store::current();
        $this->latitude = (float) $store->latitude;
        $this->longitude = (float) $store->longitude;
    }

    public function setCoordinates(float $latitude, float $longitude): void
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->calculateDeliveryFee();
    }

    public function updatedFulfillmentMethod(): void
    {
        $this->calculateDeliveryFee();
    }

    public function calculateDeliveryFee(): void
    {
        if ($this->fulfillmentMethod !== 'delivery') {
            $this->deliveryFee = 0;
            $this->distanceMeters = 0;
            $this->total = $this->subtotal;
            $this->resetErrorBag('distance');

            return;
        }

        if ($this->latitude === null || $this->longitude === null) {
            $this->deliveryFee = 0;
            $this->distanceMeters = 0;
            $this->total = $this->subtotal;

            return;
        }

        try {
            $store = Store::current();
            $origin = Coordinates::from((float) $store->latitude, (float) $store->longitude);
            $destination = Coordinates::from((float) $this->latitude, (float) $this->longitude);

            $mapProvider = app(MapProvider::class);
            $quote = $mapProvider->quoteRoute($origin, $destination);

            $this->distanceMeters = $quote->distanceMeters;

            if ($this->distanceMeters > $store->max_delivery_distance_meters) {
                $this->addError('distance', 'Jarak pengiriman melebihi radius maksimal 10 km.');
                $this->deliveryFee = 0;
            } else {
                $this->resetErrorBag('distance');
                $calculator = app(DeliveryFeeCalculator::class);
                $fee = $calculator->calculate($store->base_delivery_fee, $store->delivery_fee_per_km, $this->distanceMeters);
                $this->deliveryFee = $fee->rupiah();
            }
        } catch (\InvalidArgumentException $e) {
            $this->addError('distance', $e->getMessage());
            $this->deliveryFee = 0;
            $this->distanceMeters = 0;
        }

        $this->total = $this->subtotal + $this->deliveryFee;
    }

    public function submit(
        InventoryReservationService $reservationService,
        PaymentGateway $paymentGateway
    ): mixed {
        $this->validate();

        $cartService = new CartService;
        $cartItems = $cartService->get();

        if ($cartItems->isEmpty()) {
            $this->addError('customerName', 'Keranjang belanja Anda kosong.');

            return null;
        }

        if ($this->fulfillmentMethod === 'delivery') {
            $this->calculateDeliveryFee();
            if ($this->distanceMeters > Store::current()->max_delivery_distance_meters) {
                $this->addError('distance', 'Jarak pengiriman melebihi radius maksimal 10 km.');

                return null;
            }
        }

        // Normalize WhatsApp phone number
        try {
            $normalizedPhone = NormalizedPhone::from($this->customerWhatsapp);
        } catch (\InvalidArgumentException $e) {
            $this->addError('customerWhatsapp', $e->getMessage());

            return null;
        }

        $slot = ScheduleSlot::find($this->scheduleSlotId);
        if (! $slot) {
            $this->addError('scheduleSlotId', 'Slot waktu tidak valid.');

            return null;
        }

        try {
            $order = DB::transaction(function () use ($cartItems, $slot, $normalizedPhone, $reservationService) {
                // 1. Create order
                $orderCode = OrderCode::generate();
                $scheduledAt = Carbon::parse($slot->date->format('Y-m-d').' '.$slot->start_time);

                /** @var Order $order */
                $order = Order::create([
                    'order_code' => (string) $orderCode,
                    'customer_name' => $this->customerName,
                    'whatsapp_normalized' => (string) $normalizedPhone,
                    'whatsapp_display' => $this->customerWhatsapp,
                    'payment_status' => PaymentStatus::Pending,
                    'order_status' => OrderStatus::AwaitingPayment,
                    'fulfillment_type' => FulfillmentType::from($this->fulfillmentMethod),
                    'schedule_slot_id' => $slot->id,
                    'scheduled_at' => $scheduledAt,
                    'subtotal' => $this->subtotal,
                    'delivery_fee' => $this->deliveryFee,
                    'grand_total' => $this->total,
                    'customer_note' => $this->notes,
                    'payment_expires_at' => now()->addMinutes(30),
                ]);

                // 2. Create order items & options/addons
                foreach ($cartItems as $cartItem) {
                    /** @var OrderItem $orderItem */
                    $orderItem = $order->items()->create([
                        'product_id' => $cartItem['product']->id,
                        'product_variant_id' => $cartItem['variant']->id,
                        'product_name' => $cartItem['product']->name,
                        'variant_name' => $cartItem['variant']->name,
                        'sku' => $cartItem['variant']->sku ?? null,
                        'unit_price' => $cartItem['unit_price'],
                        'quantity' => $cartItem['quantity'],
                        'line_total' => $cartItem['total_price'],
                    ]);

                    // Save options
                    if (! empty($cartItem['options'])) {
                        foreach ($cartItem['options'] as $optionValue) {
                            $orderItem->options()->create([
                                'group_name' => $optionValue->optionGroup ? $optionValue->optionGroup->name : 'Pilihan',
                                'value_name' => $optionValue->name,
                                'price_delta' => $optionValue->price_delta,
                            ]);
                        }
                    }

                    // Save addons
                    if (! empty($cartItem['addons'])) {
                        foreach ($cartItem['addons'] as $addon) {
                            $orderItem->addons()->create([
                                'addon_name' => $addon->name,
                                'unit_price' => $addon->price,
                                'quantity' => $cartItem['quantity'],
                                'subtotal' => $addon->price * $cartItem['quantity'],
                            ]);
                        }
                    }
                }

                // 3. Create delivery record if delivery
                if ($this->fulfillmentMethod === 'delivery') {
                    $order->delivery()->create([
                        'recipient_name' => $this->customerName,
                        'recipient_phone' => (string) $normalizedPhone,
                        'address' => $this->deliveryAddress,
                        'latitude' => $this->latitude,
                        'longitude' => $this->longitude,
                        'distance_meters' => $this->distanceMeters,
                        'delivery_fee' => $this->deliveryFee,
                        'provider' => 'fake',
                    ]);
                }

                // 4. Create initial history record
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => null,
                    'to_status' => OrderStatus::AwaitingPayment,
                    'actor_type' => 'customer',
                    'reason' => 'Pemesanan baru dibuat oleh pelanggan.',
                ]);

                // 5. Reserve stock & slot
                $reservationService->reserve($order, 30);

                return $order;
            });

            // 6. Create Midtrans Snap transaction
            $paymentGateway->createTransaction($order);

            // 7. Clear cart
            $cartService->clear();

            $this->dispatch('toast', message: 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.', variant: 'success');

            // Redirect to tracking page
            return $this->redirect(
                route('orders.track', [
                    'code' => $order->order_code,
                    'phone' => (string) $normalizedPhone,
                ]),
                navigate: true
            );

        } catch (OutOfStockException $e) {
            $this->addError('customerName', $e->getMessage());
        } catch (ScheduleSlotFullException $e) {
            $this->addError('scheduleSlotId', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Checkout execution failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('customerName', 'Terjadi kesalahan sistem saat memproses pemesanan Anda: '.$e->getMessage());
        }

        return null;
    }

    public function render(): View
    {
        $cartService = new CartService;
        $cartItems = $cartService->get();

        $availableSlots = ScheduleSlot::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn ($slot) => $slot->isAvailable());

        return view('livewire.storefront.checkout-page', [
            'cartItems' => $cartItems,
            'availableSlots' => $availableSlots,
            'store' => Store::current(),
        ]);
    }
}
