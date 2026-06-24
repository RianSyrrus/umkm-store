<?php

use App\Models\ScheduleSlot;
use Carbon\CarbonInterface;

test('schedule slot attributes are cast correctly', function () {
    $slot = ScheduleSlot::factory()->create([
        'date' => '2026-06-26',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
        'quota' => 10,
        'reserved_count' => 3,
        'order_deadline' => '2026-06-25 17:00:00',
        'is_active' => true,
    ]);

    expect($slot->date)->toBeInstanceOf(CarbonInterface::class)
        ->and($slot->date->format('Y-m-d'))->toBe('2026-06-26')
        ->and($slot->order_deadline)->toBeInstanceOf(CarbonInterface::class)
        ->and($slot->order_deadline->format('Y-m-d H:i:s'))->toBe('2026-06-25 17:00:00')
        ->and($slot->quota)->toBe(10)
        ->and($slot->reserved_count)->toBe(3)
        ->and($slot->is_active)->toBeTrue();
});

test('schedule slot timeRange accessor returns formatted range', function () {
    $slot = ScheduleSlot::factory()->create([
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
    ]);

    expect($slot->timeRange)->toBe('09:00 - 12:00');
});

test('schedule slot availability check works correctly', function () {
    // 1. Available slot
    $availableSlot = ScheduleSlot::factory()->create([
        'quota' => 5,
        'reserved_count' => 2,
        'order_deadline' => now()->addHour(),
        'is_active' => true,
    ]);
    expect($availableSlot->isAvailable())->toBeTrue();

    // 2. Inactive slot
    $inactiveSlot = ScheduleSlot::factory()->create([
        'quota' => 5,
        'reserved_count' => 2,
        'order_deadline' => now()->addHour(),
        'is_active' => false,
    ]);
    expect($inactiveSlot->isAvailable())->toBeFalse();

    // 3. Fully reserved slot
    $fullSlot = ScheduleSlot::factory()->create([
        'quota' => 5,
        'reserved_count' => 5,
        'order_deadline' => now()->addHour(),
        'is_active' => true,
    ]);
    expect($fullSlot->isAvailable())->toBeFalse();

    // 4. Overdue slot
    $overdueSlot = ScheduleSlot::factory()->create([
        'quota' => 5,
        'reserved_count' => 2,
        'order_deadline' => now()->subHour(),
        'is_active' => true,
    ]);
    expect($overdueSlot->isAvailable())->toBeFalse();
});
