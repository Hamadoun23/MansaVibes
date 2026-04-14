<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceCategory extends Model
{
    protected $fillable = ['name', 'type', 'sort_order'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
