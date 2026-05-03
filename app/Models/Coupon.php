<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_uses',
        'uses_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'stackable',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value'   => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_uses'         => 'integer',
            'uses_per_user'    => 'integer',
            'used_count'       => 'integer',
            'stackable'        => 'boolean',
            'active'           => 'boolean',
            'starts_at'        => 'datetime',
            'expires_at'       => 'datetime',
        ];
    }

    public function uses(): HasMany
    {
        return $this->hasMany(CouponUse::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
