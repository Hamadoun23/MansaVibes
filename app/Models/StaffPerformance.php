<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPerformance extends Model
{
    protected $fillable = [
        'employee_id',
        'period_year',
        'period_month',
        'completed_tasks',
    ];

    protected function casts(): array
    {
        return [
            'completed_tasks' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
