<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'notes',
        'balance_cents',
    ];

    protected function casts(): array
    {
        return [
            'balance_cents' => 'integer',
        ];
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(ClientMeasurement::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
