<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CouponService
{
    /**
     * Validates a coupon code against all business rules and returns the discount amount.
     *
     * Throws ValidationException if any rule fails so the controller can return a 422.
     */
    public function validate(string $code, User $user, string $cartTotal): Coupon
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (! $coupon || ! $coupon->active) {
            throw ValidationException::withMessages(['coupon_code' => 'Cupom inválido ou inativo.']);
        }

        $now = now();

        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            throw ValidationException::withMessages(['coupon_code' => 'Este cupom ainda não está vigente.']);
        }

        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            throw ValidationException::withMessages(['coupon_code' => 'Este cupom expirou.']);
        }

        if ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
            throw ValidationException::withMessages(['coupon_code' => 'Este cupom atingiu o limite de usos.']);
        }

        if ($coupon->uses_per_user !== null) {
            $userUses = $coupon->uses()->where('user_id', $user->id)->count();

            if ($userUses >= $coupon->uses_per_user) {
                throw ValidationException::withMessages(['coupon_code' => 'Você já utilizou este cupom o número máximo de vezes.']);
            }
        }

        if ($coupon->min_order_amount !== null) {
            if (bccomp($cartTotal, (string) $coupon->min_order_amount, 2) < 0) {
                throw ValidationException::withMessages([
                    'coupon_code' => "Pedido mínimo de R$ {$coupon->min_order_amount} para este cupom.",
                ]);
            }
        }

        return $coupon;
    }

    /**
     * Calculates the discount amount for the given coupon and cart total.
     */
    public function calculateDiscount(Coupon $coupon, string $cartTotal): string
    {
        if ($coupon->discount_type === 'percentage') {
            $discount = bcmul($cartTotal, bcdiv((string) $coupon->discount_value, '100', 6), 2);
        } else {
            $discount = (string) $coupon->discount_value;
        }

        // Discount cannot exceed cart total
        return bccomp($discount, $cartTotal, 2) > 0 ? $cartTotal : $discount;
    }
}
