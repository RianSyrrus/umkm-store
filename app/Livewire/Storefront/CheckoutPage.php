<?php

namespace App\Livewire\Storefront;

use App\Models\ScheduleSlot;
use App\Models\Store;
use App\Services\Cart\CartService;
use App\Services\Maps\DeliveryFeeCalculator;
use App\Services\Maps\MapProvider;
use App\ValueObjects\Coordinates;
use Livewire\Attributes\Title;
use Livewire\Component;

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

    public function submit(): void
    {
        $this->validate();

        if ($this->fulfillmentMethod === 'delivery') {
            $this->calculateDeliveryFee();
            if ($this->distanceMeters > Store::current()->max_delivery_distance_meters) {
                $this->addError('distance', 'Jarak pengiriman melebihi radius maksimal 10 km.');

                return;
            }
        }

        $slot = ScheduleSlot::find($this->scheduleSlotId);
        if (! $slot || ! $slot->isAvailable()) {
            $this->addError('scheduleSlotId', 'Slot waktu yang dipilih sudah penuh atau melewati batas waktu pemesanan.');

            return;
        }

        // Simulate successful checkout
        $this->dispatch('toast', message: 'Checkout berhasil disimulasikan.', variant: 'success');

        // Clear cart
        (new CartService)->clear();
    }

    public function render(): \Illuminate\Contracts\View\View
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
