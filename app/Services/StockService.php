<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariation;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Reserves stock for each item in the order (decrements variation.stock and logs movement).
     *
     * Runs inside a transaction. Throws if any variation has insufficient stock.
     *
     * @param  array<int, array{variation_id: string, quantity: int}> $items
     */
    public function reserve(array $items, Order $order, User $actor): void
    {
        DB::transaction(function () use ($items, $order, $actor) {
            foreach ($items as $item) {
                $variation = ProductVariation::lockForUpdate()->find($item['variation_id']);

                if (! $variation || $variation->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Estoque insuficiente para o item SKU {$variation?->sku}.",
                    ]);
                }

                $variation->decrement('stock', $item['quantity']);

                StockMovement::create([
                    'variation_id' => $variation->id,
                    'type'         => 'out',
                    'quantity'     => $item['quantity'],
                    'reason'       => 'order placed',
                    'order_id'     => $order->id,
                    'created_by'   => $actor->id,
                ]);
            }
        });
    }

    /**
     * Releases reserved stock back when an order is cancelled.
     */
    public function release(Order $order, User $actor): void
    {
        DB::transaction(function () use ($order, $actor) {
            foreach ($order->items as $item) {
                $item->variation->increment('stock', $item->quantity);

                StockMovement::create([
                    'variation_id' => $item->variation_id,
                    'type'         => 'in',
                    'quantity'     => $item->quantity,
                    'reason'       => 'order cancelled',
                    'order_id'     => $order->id,
                    'created_by'   => $actor->id,
                ]);
            }
        });
    }

    /**
     * Applies a manual stock adjustment from the admin panel.
     */
    public function adjust(ProductVariation $variation, int $quantity, string $reason, User $actor): void
    {
        DB::transaction(function () use ($variation, $quantity, $reason, $actor) {
            $variation->increment('stock', $quantity);

            StockMovement::create([
                'variation_id' => $variation->id,
                'type'         => 'adjustment',
                'quantity'     => abs($quantity),
                'reason'       => $reason,
                'created_by'   => $actor->id,
            ]);
        });
    }
}
