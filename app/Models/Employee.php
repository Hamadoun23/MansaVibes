<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'role_title',
        'monthly_salary_cents',
    ];

    protected function casts(): array
    {
        return [
            'monthly_salary_cents' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
