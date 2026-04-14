<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OrderImage extends Model
{
    protected $fillable = [
        'order_id',
        'path',
        'caption',
        'sort_order',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
