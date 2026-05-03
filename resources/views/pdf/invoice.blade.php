<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedido #{{ $order->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #f3f4f6; }
        .total { font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <h2>Comprovante de Pedido</h2>
    <p><strong>Pedido:</strong> #{{ $order->id }}</p>
    <p><strong>Data:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
    <p><strong>Cliente:</strong> {{ $order->user->name }} — {{ $order->user->email }}</p>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Tamanho</th>
                <th>Cor</th>
                <th>Qtd</th>
                <th>Preço unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->quantity }}</td>
                <td>R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="total">Total</td>
                <td class="total">R$ {{ number_format($order->total, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top:16px;"><strong>Endereço de entrega:</strong><br>
        {{ $order->shipping_address['street'] }}, {{ $order->shipping_address['number'] }}<br>
        {{ $order->shipping_address['district'] }} — {{ $order->shipping_address['city'] }}/{{ $order->shipping_address['state'] }}<br>
        CEP {{ $order->shipping_address['zip_code'] }}
    </p>
</body>
</html>
