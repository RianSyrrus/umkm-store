<?php

namespace App\Models;

use Database\Factories\ScheduleSlotFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleSlot extends Model
{
    /** @use HasFactory<ScheduleSlotFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'order_deadline' => 'datetime',
        'is_active' => 'boolean',
        'quota' => 'integer',
        'reserved_count' => 'integer',
    ];

    /**
     * Check if the slot still has vacancy and has not passed its ordering deadline.
     */
    public function isAvailable(): bool
    {
        return $this->is_active
            && $this->reserved_count < $this->quota
            && now()->lt($this->order_deadline);
    }

    /**
     * Accessor to get formatted time range, e.g. "09:00 - 12:00"
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<non-falsy-string, never>
     */
    protected function timeRange(): Attribute
    {
        return Attribute::get(
            fn (): string => substr($this->start_time, 0, 5).' - '.substr($this->end_time, 0, 5)
        );
    }
}
