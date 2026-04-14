<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'threshold',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'threshold' => 'decimal:3',
            'active' => 'boolean',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
