<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture commande {{ $order->reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 8px; }
        .muted { color: #555; }
        table.lines { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.lines th, table.lines td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        table.lines th { background: #f0f0f0; font-size: 10px; }
        .right { text-align: right; }
        .box { margin-top: 14px; padding: 8px; background: #f9f9f9; border: 1px solid #ddd; }
    </style>
</head>
<body>
    @php
        $issuerName = $order->tenant?->name ?? config('app.name', 'Mansa Vibes');
    @endphp
    <div style="margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #ccc;">
        <h1 style="margin:0 0 4px;font-size:17px;">{{ $issuerName }}</h1>
        <p class="muted" style="margin:0;font-size:11px;">Émetteur — Facture / bon de commande</p>
    </div>
    <p class="muted">Réf. {{ $order->reference }} | {{ now()->format('d/m/Y H:i') }}</p>
    <p><strong>Client :</strong> {{ $order->client?->name ?? '—' }}
        @if ($order->client?->phone) — Tél. {{ $order->client->phone }} @endif
    </p>
    @if ($order->client?->email)
        <p class="muted">Email : {{ $order->client->email }}</p>
    @endif
    <p><strong>Modèles :</strong> {{ $order->displayModelLabel() }}</p>
    <p class="muted">Livraison : {{ $order->deliveryModeLabel() }} | Statut : {{ $order->statusLabel() }}</p>

    <table class="lines">
        <thead>
            <tr>
                <th>Modèle</th>
                <th>Description</th>
                <th class="right">Qté</th>
                <th class="right">PU (FCFA)</th>
                <th class="right">Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->measurementTemplate?->name ?? '—' }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price_cents / 100, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($item->lineNetCents() / 100, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $grossCents = $order->subtotalGrossCents();
        $lineDiscCents = (int) $order->items->sum(fn ($i) => $i->effectiveLineDiscountCents());
        $orderDiscCents = (int) ($order->order_discount_cents ?? 0);
        $discScope = $order->discount_scope === 'order' ? 'all' : $order->discount_scope;
        $discPct = (int) ($order->discount_percent ?? 0);
    @endphp

    <p class="right muted" style="margin-top:10px;">Sous-total brut : {{ number_format($grossCents / 100, 0, ',', ' ') }} FCFA</p>
    @if ($discScope === 'all' && $discPct > 0 && $orderDiscCents > 0)
        <p class="right muted">Remise commande ({{ $discPct }} %) : −{{ number_format($orderDiscCents / 100, 0, ',', ' ') }} FCFA</p>
    @elseif ($discScope === 'lines' && $discPct > 0 && $lineDiscCents > 0)
        <p class="right muted">Remise lignes ({{ $discPct }} %) : −{{ number_format($lineDiscCents / 100, 0, ',', ' ') }} FCFA</p>
    @endif
    <p class="right" style="font-size:13px;margin-top:8px;"><strong>Total TTC : {{ number_format($order->total_cents / 100, 0, ',', ' ') }} FCFA</strong></p>

    <div class="box">
        <p><strong>Montant versé :</strong> {{ number_format($order->advance_payment_cents / 100, 0, ',', ' ') }} FCFA
            @if ($order->paymentMethodLabel()) ({{ $order->paymentMethodLabel() }}) @endif
        </p>
        <p><strong>Reste à payer :</strong> {{ number_format($order->balanceDueCents() / 100, 0, ',', ' ') }} FCFA</p>
        <p>
            <strong>Paiement :</strong>
            @if ((int) $order->total_cents <= 0)
                —
            @elseif ($order->isFullyPaid())
                Payé
            @else
                Acompte / partiel
            @endif
        </p>
    </div>
</body>
</html>
