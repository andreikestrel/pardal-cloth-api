<?php

namespace App\Jobs;

use App\Models\ProductVariation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AlertLowStock implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ProductVariation $variation,
    ) {}

    public function handle(): void
    {
        // TODO: implement mail in Phase 7
        // Mail::to(config('mail.admin_address'))->send(new LowStockAlert($this->variation));
    }
}
