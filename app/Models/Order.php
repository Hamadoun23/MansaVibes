<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'client_id',
        'reference',
        'model_name',
        'measurement_form_template_id',
        'status',
        'due_date',
        'assigned_to',
        'total_cents',
        'advance_payment_cents',
        'payment_method',
        'delivery_mode',
        'discount_scope',
        'order_discount_cents',
        'discount_percent',
        'model_notes',
        'notes',
        'inventory_deducted_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'inventory_deducted_at' => 'datetime',
            'total_cents' => 'integer',
            'advance_payment_cents' => 'integer',
            'order_discount_cents' => 'integer',
            'discount_percent' => 'integer',
        ];
    }

    public function balanceDueCents(): int
    {
        return max(0, (int) $this->total_cents - (int) $this->advance_payment_cents);
    }

    /**
     * Paiement intégral (montant versé ≥ total TTC).
     */
    public function isFullyPaid(): bool
    {
        $total = (int) $this->total_cents;

        return $total > 0 && (int) $this->advance_payment_cents >= $total;
    }

    /** @return array<string, string> */
    public static function paymentMethodLabels(): array
    {
        return [
            'cash' => __('order.payment.cash'),
            'orange_money' => __('order.payment.orange_money'),
            'wave' => __('order.payment.wave'),
            'bank_transfer' => __('order.payment.bank_transfer'),
        ];
    }

    public function paymentMethodLabel(): ?string
    {
        $method = $this->payment_method;

        if ($method === null || $method === '') {
            return null;
        }

        return self::paymentMethodLabels()[$method] ?? $method;
    }

    /** @return array<string, string> */
    public static function deliveryModeLabels(): array
    {
        return [
            'pickup' => __('order.delivery.pickup'),
            'delivery' => __('order.delivery.delivery'),
        ];
    }

    public function deliveryModeLabel(): string
    {
        $mode = $this->delivery_mode ?? 'pickup';

        return self::deliveryModeLabels()[$mode] ?? $mode;
    }

    /** @return array<string, string> */
    public static function statusLabels(): array
    {
        return [
            'pending' => __('order.status.pending'),
            'in_progress' => __('order.status.in_progress'),
            'done' => __('order.status.done'),
            'validated' => __('order.status.validated'),
            'delivered' => __('order.status.delivered'),
        ];
    }

    public static function statusLabelFor(?string $status): string
    {
        $s = $status ?? 'pending';

        return self::statusLabels()[$s] ?? (string) $s;
    }

    public function statusLabel(): string
    {
        return self::statusLabelFor($this->status);
    }

    /**
     * Numéro international pour wa.me (chiffres uniquement, sans +).
     */
    public function clientWhatsAppDigits(): ?string
    {
        $this->loadMissing('client');
        $raw = $this->client?->phone;
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $raw);

        return ($digits !== null && $digits !== '') ? $digits : null;
    }

    public function subtotalGrossCents(): int
    {
        $this->loadMissing('items');

        return (int) $this->items->sum(fn (OrderItem $i) => (int) $i->quantity * (int) $i->unit_price_cents);
    }

    public function recalculateTotals(): void
    {
        $this->load('items');
        $subtotal = (int) $this->items->sum(fn (OrderItem $i) => $i->lineGrossCents());
        $scope = $this->discount_scope ?? 'none';
        if ($scope === 'order') {
            $scope = 'all';
        }
        $p = min(100, max(0, (int) ($this->discount_percent ?? 0)));

        if ($p === 0 || $scope === 'none') {
            $this->order_discount_cents = 0;
            foreach ($this->items as $i) {
                $i->discount_cents = 0;
                $i->saveQuietly();
            }
            $this->total_cents = $subtotal;
            $this->saveQuietly();

            return;
        }

        if ($scope === 'all') {
            $disc = (int) round($subtotal * $p / 100);
            $this->order_discount_cents = $disc;
            $this->total_cents = max(0, $subtotal - $disc);
            foreach ($this->items as $i) {
                $i->discount_cents = 0;
                $i->saveQuietly();
            }
            $this->saveQuietly();

            return;
        }

        $net = 0;
        $totalDisc = 0;
        foreach ($this->items as $i) {
            $gross = $i->lineGrossCents();
            if ($i->discount_applies) {
                $lineDisc = (int) round($gross * $p / 100);
                $lineDisc = min($lineDisc, $gross);
                $i->discount_cents = $lineDisc;
                $net += $gross - $lineDisc;
                $totalDisc += $lineDisc;
            } else {
                $i->discount_cents = 0;
                $net += $gross;
            }
            $i->saveQuietly();
        }
        $this->order_discount_cents = $totalDisc;
        $this->total_cents = max(0, $net);
        $this->saveQuietly();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function measurementTemplate(): BelongsTo
    {
        return $this->belongsTo(MeasurementFormTemplate::class, 'measurement_form_template_id');
    }

    public function displayModelLabel(): string
    {
        $this->loadMissing('items.measurementTemplate');
        $names = $this->items
            ->map(fn (OrderItem $i) => $i->measurementTemplate?->name)
            ->filter()
            ->unique()
            ->values();
        if ($names->isNotEmpty()) {
            return $names->implode(', ');
        }

        return $this->measurementTemplate?->name
            ?? $this->model_name
            ?? '—';
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(OrderImage::class)->orderBy('sort_order');
    }
}
