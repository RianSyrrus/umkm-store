<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OptionGroup extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_required' => 'boolean',
        'min_selected' => 'integer',
        'max_selected' => 'integer',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(OptionValue::class)->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_option_groups')
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}
