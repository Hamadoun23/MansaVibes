<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 12px; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>MANSA VIBES — Facture</h1>
    <p class="muted">N° {{ $invoice->number }} | Statut : {{ $invoice->status }}</p>
    <p><strong>Client :</strong> {{ $invoice->client?->name }}</p>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Qté</th>
                <th class="right">PU</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                @php($line = $item->quantity * $item->unit_price_cents)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price_cents / 100, 2, ',', ' ') }} €</td>
                    <td class="right">{{ number_format($line / 100, 2, ',', ' ') }} €</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="right" style="margin-top:12px;font-size:14px;"><strong>Total TTC (indicatif) : {{ number_format($invoice->total_cents / 100, 2, ',', ' ') }} €</strong></p>
</body>
</html>
