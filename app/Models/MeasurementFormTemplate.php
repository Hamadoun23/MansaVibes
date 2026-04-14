<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeasurementFormTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'notes',
        'fields',
        'sort_order',
        'is_active',
        'reference_price_fcfa',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'is_active' => 'boolean',
            'reference_price_fcfa' => 'integer',
        ];
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(ClientMeasurement::class, 'measurement_form_template_id');
    }

    /**
     * @return list<array{key: string, label: string, unit: string, type: string}>
     */
    public function normalizedFields(): array
    {
        $raw = $this->fields ?? [];

        return collect($raw)->map(function ($row): array {
            if (! is_array($row)) {
                return ['key' => '', 'label' => '', 'unit' => '', 'type' => 'number'];
            }

            $type = $row['type'] ?? 'number';
            $type = $type === 'text' ? 'text' : 'number';

            return [
                'key' => preg_replace('/[^a-z0-9_]/', '', strtolower((string) ($row['key'] ?? ''))),
                'label' => trim((string) ($row['label'] ?? '')),
                'unit' => trim((string) ($row['unit'] ?? '')),
                'type' => $type,
            ];
        })->filter(fn (array $f) => $f['key'] !== '' && $f['label'] !== '')
            ->values()
            ->all();
    }

    public static function seedDefaultsForTenantId(int $tenantId): void
    {
        if (static::query()->withoutGlobalScopes()->where('tenant_id', $tenantId)->exists()) {
            return;
        }

        $sets = [
            [
                'name' => 'Robe & jupe femme',
                'sort_order' => 1,
                'reference_price_fcfa' => 45_000,
                'fields' => [
                    ['key' => 'poitrine', 'label' => 'Poitrine', 'unit' => 'cm', 'type' => 'number'],
                    ['key' => 'taille', 'label' => 'Taille', 'unit' => 'cm', 'type' => 'number'],
                    ['key' => 'hanche', 'label' => 'Hanche', 'unit' => 'cm', 'type' => 'number'],
                    ['key' => 'longueur', 'label' => 'Longueur', 'unit' => 'cm', 'type' => 'number'],
                    ['key' => 'epaule', 'label' => 'Épaule', 'unit' => 'cm', 'type' => 'number'],
                ],
            ],
            [
                'name' => 'Costume homme',
                'sort_order' => 2,
                'reference_price_fcfa' => 65_000,
                'fields' => [
                    ['key' => 'poitrine', 'label' => 'Poitrine', 'unit' => 'cm', 'type' => 'number'],
                    ['key' => 'taille', 'label' => 'Taille', 'unit' => 'cm', 'type' => 'number'],
                    ['key' => 'epaule', 'label' => 'Épaule', 'unit' => 'cm', 'type' => 'number'],
                    ['key' => 'longueur_veste', 'label' => 'Longueur veste', 'unit' => 'cm', 'type' => 'number'],
                    ['key' => 'tour_bras', 'label' => 'Tour de bras', 'unit' => 'cm', 'type' => 'number'],
                ],
            ],
            [
                'name' => 'Enfant / pièce simple',
                'sort_order' => 3,
                'reference_price_fcfa' => 18_000,
                'fields' => [
                    ['key' => 'taille', 'label' => 'Taille', 'unit' => 'cm', 'type' => 'number'],
                    ['key' => 'longueur', 'label' => 'Longueur', 'unit' => 'cm', 'type' => 'number'],
                    ['key' => 'tour_poitrine', 'label' => 'Tour de poitrine', 'unit' => 'cm', 'type' => 'number'],
                    ['key' => 'commentaire', 'label' => 'Commentaire', 'unit' => '', 'type' => 'text'],
                ],
            ],
        ];

        foreach ($sets as $row) {
            static::query()->withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'name' => $row['name'],
                'fields' => $row['fields'],
                'sort_order' => $row['sort_order'],
                'is_active' => true,
                'reference_price_fcfa' => (int) ($row['reference_price_fcfa'] ?? 0),
            ]);
        }
    }

    public static function ensureDefaultsForAuthenticatedTenant(): void
    {
        $user = auth()->user();
        if ($user === null || $user->tenant_id === null) {
            return;
        }

        if (static::query()->where('tenant_id', $user->tenant_id)->exists()) {
            return;
        }

        static::seedDefaultsForTenantId((int) $user->tenant_id);
    }
}
