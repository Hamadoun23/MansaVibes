<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'inventory_item_id',
        'inventory_characteristic_key',
        'inventory_consumed_meters',
        'measurement_form_template_id',
        'description',
        'quantity',
        'unit_price_cents',
        'discount_cents',
        'discount_applies',
        'client_supplies_fabric',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'inventory_consumed_meters' => 'decimal:3',
            'unit_price_cents' => 'integer',
            'discount_cents' => 'integer',
            'discount_applies' => 'boolean',
            'client_supplies_fabric' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function measurementTemplate(): BelongsTo
    {
        return $this->belongsTo(MeasurementFormTemplate::class, 'measurement_form_template_id');
    }

    public function lineGrossCents(): int
    {
        return (int) $this->quantity * (int) $this->unit_price_cents;
    }

    public function effectiveLineDiscountCents(): int
    {
        return min(max(0, (int) $this->discount_cents), $this->lineGrossCents());
    }

    public function lineNetCents(): int
    {
        return max(0, $this->lineGrossCents() - $this->effectiveLineDiscountCents());
    }
}
