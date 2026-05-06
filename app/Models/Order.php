<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'source',
        'cash_session_id',
        'status',
        'subtotal',
        'discount_promotions',
        'discount_coupon',
        'coupon_id',
        'total',
        'shipping_address',
        'notes',
        'pdv_customer_name',
        'pdv_customer_doc',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'            => 'decimal:2',
            'discount_promotions' => 'decimal:2',
            'discount_coupon'     => 'decimal:2',
            'total'               => 'decimal:2',
            'shipping_address'    => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function couponUse(): HasOne
    {
        return $this->hasOne(CouponUse::class);
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }
}
