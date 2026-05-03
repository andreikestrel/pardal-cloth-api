<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'trigger_type',
        'discount_type',
        'discount_value',
        'min_items',
        'min_amount',
        'target_id',
        'active',
        'starts_at',
        'ends_at',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_amount'     => 'decimal:2',
            'min_items'      => 'integer',
            'priority'       => 'integer',
            'active'         => 'boolean',
            'starts_at'      => 'datetime',
            'ends_at'        => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        if (! $this->active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }
}
