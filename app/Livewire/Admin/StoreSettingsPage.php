<?php

namespace App\Livewire\Admin;

use App\Models\Store;
use App\ValueObjects\NormalizedPhone;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pengaturan Toko')]
class StoreSettingsPage extends Component
{
    public string $name = '';

    public string $whatsapp = '';

    public string $address = '';

    public float $latitude = 0.0;

    public float $longitude = 0.0;

    public int $baseDeliveryFee = 0;

    public int $deliveryFeePerKm = 0;

    public int $lowStockThreshold = 5;

    protected array $rules = [
        'name' => ['required', 'string', 'max:150'],
        'whatsapp' => ['required', 'string', 'max:20'],
        'address' => ['required', 'string', 'max:1000'],
        'latitude' => ['required', 'numeric', 'between:-90,90'],
        'longitude' => ['required', 'numeric', 'between:-180,180'],
        'baseDeliveryFee' => ['required', 'integer', 'min:0'],
        'deliveryFeePerKm' => ['required', 'integer', 'min:0'],
        'lowStockThreshold' => ['required', 'integer', 'min:1'],
    ];

    public function mount(): void
    {
        $store = Store::query()->first();
        if ($store) {
            $this->name = $store->name;
            $this->whatsapp = $store->whatsapp;
            $this->address = $store->address;
            $this->latitude = (float) $store->latitude;
            $this->longitude = (float) $store->longitude;
            $this->baseDeliveryFee = (int) $store->base_delivery_fee;
            $this->deliveryFeePerKm = (int) $store->delivery_fee_per_km;
            $this->lowStockThreshold = (int) $store->low_stock_threshold;
        }
    }

    public function save(): void
    {
        $this->validate();

        try {
            $normalizedWhatsapp = (string) NormalizedPhone::from($this->whatsapp);
        } catch (\InvalidArgumentException $e) {
            $this->addError('whatsapp', $e->getMessage());

            return;
        }

        $store = Store::query()->first();
        if (! $store) {
            $store = new Store;
            $store->slug = Str::slug($this->name);
        }

        $store->fill([
            'name' => $this->name,
            'whatsapp' => $normalizedWhatsapp,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'base_delivery_fee' => $this->baseDeliveryFee,
            'delivery_fee_per_km' => $this->deliveryFeePerKm,
            'low_stock_threshold' => $this->lowStockThreshold,
            'max_delivery_distance_meters' => 10000,
        ]);

        $store->save();

        $this->whatsapp = $normalizedWhatsapp;

        $this->dispatch('toast', message: 'Pengaturan toko berhasil diperbarui.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.admin.store-settings-page');
    }
}
