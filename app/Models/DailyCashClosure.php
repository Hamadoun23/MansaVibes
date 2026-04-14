<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyCashClosure extends Model
{
    protected $fillable = [
        'closed_on',
        'opening_cents',
        'closing_cents',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'closed_on' => 'date',
            'opening_cents' => 'integer',
            'closing_cents' => 'integer',
        ];
    }
}
