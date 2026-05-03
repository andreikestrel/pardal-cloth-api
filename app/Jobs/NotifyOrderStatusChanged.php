<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class NotifyOrderStatusChanged implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
        public readonly string $newStatus,
        public readonly ?string $note = null,
    ) {}

    /**
     * Posts the status update to pardal-cloth-ws so Socket.IO
     * can push it to the customer's browser in real time.
     */
    public function handle(): void
    {
        $wsUrl    = rtrim(config('services.ws.url'), '/');
        $secret   = config('services.ws.internal_secret');

        Http::withHeaders(['x-internal-secret' => $secret])
            ->post("{$wsUrl}/internal/notify", [
                'room'  => "order:{$this->order->id}",
                'event' => 'order:status_updated',
                'data'  => [
                    'status'     => $this->newStatus,
                    'label'      => $this->statusLabel($this->newStatus),
                    'note'       => $this->note,
                    'updated_at' => now()->toIso8601String(),
                ],
            ]);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending'   => 'Aguardando pagamento',
            'confirmed' => 'Confirmado',
            'preparing' => 'Em preparo',
            'shipped'   => 'Enviado',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelado',
            default     => $status,
        };
    }
}
