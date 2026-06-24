<?php

use App\Livewire\Admin\ScheduleSlotIndex;
use App\Models\ScheduleSlot;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from schedule slots page', function () {
    $this->get(route('admin.schedule-slots'))->assertRedirect(route('login'));
});

test('admin can manage schedule slots', function () {
    $this->actingAs(User::factory()->create());

    // 1. Create a schedule slot
    $date = now()->addDay()->format('Y-m-d');
    $orderDeadline = now()->addDay()->startOfDay()->subHours(2)->format('Y-m-d\TH:i');

    Livewire::test(ScheduleSlotIndex::class)
        ->set('date', $date)
        ->set('startTime', '09:00')
        ->set('endTime', '12:00')
        ->set('quota', 15)
        ->set('orderDeadline', $orderDeadline)
        ->set('isActive', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('date', '')
        ->assertSet('quota', 10); // default quota resets to 10

    $slot = ScheduleSlot::first();
    expect($slot)->not->toBeNull()
        ->and($slot->date->format('Y-m-d'))->toBe($date)
        ->and($slot->start_time)->toBe('09:00:00')
        ->and($slot->end_time)->toBe('12:00:00')
        ->and($slot->quota)->toBe(15)
        ->and($slot->is_active)->toBeTrue();

    // 2. Edit the schedule slot
    Livewire::test(ScheduleSlotIndex::class)
        ->call('editSlot', $slot->id)
        ->assertSet('editingSlotId', $slot->id)
        ->assertSet('quota', 15)
        ->set('quota', 20)
        ->call('save')
        ->assertHasNoErrors();

    expect($slot->fresh()->quota)->toBe(20);

    // 3. Delete the schedule slot
    Livewire::test(ScheduleSlotIndex::class)
        ->call('deleteSlot', $slot->id);

    expect(ScheduleSlot::find($slot->id))->toBeNull();
});
