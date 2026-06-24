<?php

namespace App\Livewire\Admin;

use App\Models\ScheduleSlot;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kelola Slot Jadwal')]
class ScheduleSlotIndex extends Component
{
    use WithPagination;

    public string $date = '';

    public string $startTime = '09:00';

    public string $endTime = '12:00';

    public int $quota = 10;

    public string $orderDeadline = '';

    public bool $isActive = true;

    public ?int $editingSlotId = null;

    public string $search = '';

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'startTime' => ['required', 'string'],
            'endTime' => ['required', 'string'],
            'quota' => ['required', 'integer', 'min:1'],
            'orderDeadline' => ['required', 'date'],
            'isActive' => ['boolean'],
        ];
    }

    /** @var array<string, string> */
    protected array $validationAttributes = [
        'date' => 'Tanggal',
        'startTime' => 'Waktu Mulai',
        'endTime' => 'Waktu Selesai',
        'quota' => 'Kuota',
        'orderDeadline' => 'Batas Waktu Pemesanan',
        'isActive' => 'Status Aktif',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $this->validate();

        // Additional time validation: end_time must be after start_time
        if (Carbon::parse($this->endTime)->lte(Carbon::parse($this->startTime))) {
            $this->addError('endTime', 'Waktu selesai harus setelah waktu mulai.');

            return;
        }

        $data = [
            'date' => $this->date,
            'start_time' => Carbon::parse($this->startTime)->format('H:i:s'),
            'end_time' => Carbon::parse($this->endTime)->format('H:i:s'),
            'quota' => $this->quota,
            'order_deadline' => Carbon::parse($this->orderDeadline),
            'is_active' => $this->isActive,
        ];

        if ($this->editingSlotId) {
            $slot = ScheduleSlot::findOrFail($this->editingSlotId);
            $slot->update($data);
            $this->dispatch('toast', message: 'Slot jadwal berhasil diperbarui.', variant: 'success');
        } else {
            ScheduleSlot::create($data);
            $this->dispatch('toast', message: 'Slot jadwal berhasil ditambahkan.', variant: 'success');
        }

        $this->resetInputFields();
    }

    public function editSlot(int $id): void
    {
        $slot = ScheduleSlot::findOrFail($id);
        $this->editingSlotId = $slot->id;
        $this->date = $slot->date->format('Y-m-d');
        $this->startTime = substr($slot->start_time, 0, 5);
        $this->endTime = substr($slot->end_time, 0, 5);
        $this->quota = $slot->quota;
        $this->orderDeadline = $slot->order_deadline->format('Y-m-d\TH:i');
        $this->isActive = $slot->is_active;
    }

    public function deleteSlot(int $id): void
    {
        $slot = ScheduleSlot::findOrFail($id);
        $slot->delete();

        $this->dispatch('toast', message: 'Slot jadwal berhasil dihapus.', variant: 'success');
        $this->resetInputFields();
    }

    public function resetInputFields(): void
    {
        $this->date = '';
        $this->startTime = '09:00';
        $this->endTime = '12:00';
        $this->quota = 10;
        $this->orderDeadline = '';
        $this->isActive = true;
        $this->editingSlotId = null;
        $this->resetErrorBag();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $scheduleSlots = ScheduleSlot::query()
            ->when($this->search, function ($q) {
                $q->where('date', 'like', '%'.$this->search.'%');
            })
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(10);

        return view('livewire.admin.schedule-slot-index', [
            'scheduleSlots' => $scheduleSlots,
        ]);
    }
}
