<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    protected $fillable = [
        'name',
        'acquisition_date',
        'amount_cents',
        'useful_life_months',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'amount_cents' => 'integer',
            'useful_life_months' => 'integer',
        ];
    }
}
