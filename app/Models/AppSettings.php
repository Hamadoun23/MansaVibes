<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSettings extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'business_name',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public static function businessName(): string
    {
        $name = static::query()->value('business_name');

        return ($name !== null && $name !== '') ? (string) $name : (string) config('app.name');
    }

    /** @return array<string, mixed> */
    public static function settingsArray(): array
    {
        $row = static::query()->first();
        $s = $row?->settings;

        return is_array($s) ? $s : [];
    }
}
