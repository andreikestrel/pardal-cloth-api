<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
    ) {}

    public function handle(): void
    {
        // TODO: implement mail in Phase 7
        // Mail::to($this->order->user)->send(new OrderConfirmation($this->order));
    }
}
