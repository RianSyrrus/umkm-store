<?php

namespace App\Livewire\Storefront;

use App\Models\Order;
use App\ValueObjects\NormalizedPhone;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Lacak Pesanan')]
class OrderTrackingPage extends Component
{
    #[Url(as: 'code')]
    public string $searchCode = '';

    #[Url(as: 'phone')]
    public string $searchPhone = '';

    public ?Order $order = null;

    public string $errorMessage = '';

    public function mount(): void
    {
        if (filled($this->searchCode) && filled($this->searchPhone)) {
            $this->track();
        }
    }

    /**
     * Search and track the order details.
     */
    public function track(): void
    {
        $this->errorMessage = '';
        $this->order = null;

        $this->validate([
            'searchCode' => ['required', 'string'],
            'searchPhone' => ['required', 'string'],
        ], [
            'searchCode.required' => 'Kode pesanan wajib diisi.',
            'searchPhone.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        try {
            $normalizedPhone = NormalizedPhone::from($this->searchPhone);
        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = 'Format nomor WhatsApp tidak valid.';

            return;
        }

        $order = Order::with(['items.options', 'items.addons', 'delivery', 'payment', 'statusHistories'])
            ->where('order_code', trim($this->searchCode))
            ->where('whatsapp_normalized', (string) $normalizedPhone)
            ->first();

        if (! $order) {
            $this->errorMessage = 'Pesanan tidak ditemukan. Periksa kembali kode pesanan dan nomor WhatsApp Anda.';

            return;
        }

        $this->order = $order;
    }

    /**
     * Refresh the order status dynamically.
     */
    public function refreshOrder(): void
    {
        if ($this->order) {
            $this->order->load(['items.options', 'items.addons', 'delivery', 'payment', 'statusHistories']);
        }
    }

    public function render(): View
    {
        return view('livewire.storefront.order-tracking-page', [
            'clientKey' => config('services.midtrans.client_key'),
            'isProduction' => config('services.midtrans.is_production'),
        ]);
    }
}
