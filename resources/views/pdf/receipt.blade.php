<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Courier New', Courier, monospace;
        font-size: 11px;
        color: #111;
        width: 100%;
        padding: 8px;
    }
    .center  { text-align: center; }
    .right   { text-align: right; }
    .bold    { font-weight: bold; }
    .divider { border-top: 1px dashed #555; margin: 6px 0; }
    .spacer  { margin: 4px 0; }
    table    { width: 100%; border-collapse: collapse; }
    td       { vertical-align: top; padding: 1px 0; }
    .item-name { width: 55%; }
    .item-qty  { width: 15%; text-align: center; }
    .item-total{ width: 30%; text-align: right; }
    .summary-label { width: 65%; }
    .summary-value { width: 35%; text-align: right; }
    .total-row td  { font-weight: bold; font-size: 13px; }
</style>
</head>
<body>

<div class="center bold" style="font-size:13px; margin-bottom:2px;">
    {{ $settings?->company_name ?? 'Pardal Cloth' }}
</div>

@if($settings?->company_address)
<div class="center" style="font-size:10px; color:#444;">
    {{ $settings->company_address }}
</div>
@endif

<div class="divider"></div>

<table>
    <tr>
        <td>Venda #{{ strtoupper(substr($order->id, 0, 8)) }}</td>
        <td class="right">{{ $order->created_at->format('d/m/Y H:i') }}</td>
    </tr>
    <tr>
        <td>Operador: {{ $order->cashSession?->operator?->name ?? '—' }}</td>
        <td class="right">{{ $order->cashSession?->cashRegister?->name ?? '—' }}</td>
    </tr>
</table>

@if($order->pdv_customer_name)
<div class="spacer">
    Cliente: {{ $order->pdv_customer_name }}
    @if($order->pdv_customer_doc)
        — CPF: {{ $order->pdv_customer_doc }}
    @endif
</div>
@endif

<div class="divider"></div>

<table>
    <thead>
        <tr>
            <td class="item-name bold">Item</td>
            <td class="item-qty bold">Qtd</td>
            <td class="item-total bold">Total</td>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td class="item-name">
                {{ $item->variation?->product?->name ?? 'Produto' }}
                @if($item->variation?->size || $item->variation?->color)
                    <br><span style="font-size:10px; color:#444;">
                        {{ implode('/', array_filter([$item->variation->size, $item->variation->color])) }}
                    </span>
                @endif
            </td>
            <td class="item-qty">{{ $item->quantity }}x</td>
            <td class="item-total">
                R$ {{ number_format($item->subtotal, 2, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="divider"></div>

<table>
    <tr>
        <td class="summary-label">Subtotal</td>
        <td class="summary-value">R$ {{ number_format($order->subtotal, 2, ',', '.') }}</td>
    </tr>
    @if($order->discount_promotions > 0)
    <tr>
        <td class="summary-label">Desconto promoção</td>
        <td class="summary-value">- R$ {{ number_format($order->discount_promotions, 2, ',', '.') }}</td>
    </tr>
    @endif
    @if($order->discount_coupon > 0)
    <tr>
        <td class="summary-label">
            Cupom@if($order->coupon) ({{ $order->coupon->code }})@endif
        </td>
        <td class="summary-value">- R$ {{ number_format($order->discount_coupon, 2, ',', '.') }}</td>
    </tr>
    @endif
    @php
        $manualDiscount = bcsub(
            bcadd($order->subtotal - $order->discount_promotions - $order->discount_coupon, 0, 2),
            $order->total,
            2
        );
    @endphp
    @if($manualDiscount > 0)
    <tr>
        <td class="summary-label">Desconto manual</td>
        <td class="summary-value">- R$ {{ number_format($manualDiscount, 2, ',', '.') }}</td>
    </tr>
    @endif
    <tr class="total-row">
        <td class="summary-label">TOTAL</td>
        <td class="summary-value">R$ {{ number_format($order->total, 2, ',', '.') }}</td>
    </tr>
</table>

<div class="divider"></div>

@if($order->payment)
<div class="spacer">
    Pagamento:
    @php
        $methodLabels = ['pix' => 'Pix', 'credit_card' => 'Cartão de crédito', 'cash' => 'Dinheiro'];
    @endphp
    {{ $methodLabels[$order->payment->method] ?? $order->payment->method }}
</div>
@endif

<div class="divider"></div>
<div class="center spacer">Obrigado pela preferência!</div>

</body>
</html>
