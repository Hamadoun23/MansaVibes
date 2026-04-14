<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    protected $fillable = [
        'finance_category_id',
        'direction',
        'amount_cents',
        'label',
        'movement_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'movement_date' => 'date',
            'amount_cents' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCategory::class, 'finance_category_id');
    }
}
