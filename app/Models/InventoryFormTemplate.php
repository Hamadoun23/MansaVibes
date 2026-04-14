<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryFormTemplate extends Model
{
    protected $fillable = [
        'name',
        'applies_to_stock_type',
        'fields',
        'sort_order',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'inventory_form_template_id');
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
            $type = in_array($type, ['number', 'text'], true) ? $type : 'number';

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

    public static function seedDefaultsIfEmpty(): void
    {
        if (static::query()->exists()) {
            return;
        }

        $sets = [
            [
                'name' => 'Fiche tissu / matière',
                'applies_to_stock_type' => 'fabric',
                'sort_order' => 1,
                'fields' => [
                    ['key' => 'couleurs', 'label' => 'Couleurs disponibles', 'unit' => '', 'type' => 'text'],
                    ['key' => 'qualite', 'label' => 'Qualité / composition', 'unit' => '', 'type' => 'text'],
                    ['key' => 'description', 'label' => 'Description (motifs, usage, entretien…)', 'unit' => '', 'type' => 'text'],
                    ['key' => 'largeur_utile', 'label' => 'Largeur utile', 'unit' => 'cm', 'type' => 'number'],
                ],
            ],
            [
                'name' => 'Fiche accessoire / mercerie',
                'applies_to_stock_type' => 'accessory',
                'sort_order' => 2,
                'fields' => [
                    ['key' => 'taille_dim', 'label' => 'Taille / dimension', 'unit' => '', 'type' => 'text'],
                    ['key' => 'materiau', 'label' => 'Matériau / finition', 'unit' => '', 'type' => 'text'],
                    ['key' => 'reference_fournisseur', 'label' => 'Réf. fournisseur', 'unit' => '', 'type' => 'text'],
                ],
            ],
            [
                'name' => 'Fiche stock (autre)',
                'applies_to_stock_type' => 'other',
                'sort_order' => 3,
                'fields' => [
                    ['key' => 'caracteristique_1', 'label' => 'Caractéristique', 'unit' => '', 'type' => 'text'],
                    ['key' => 'caracteristique_2', 'label' => 'Détail', 'unit' => '', 'type' => 'text'],
                ],
            ],
        ];

        foreach ($sets as $row) {
            static::query()->create([
                'name' => $row['name'],
                'applies_to_stock_type' => $row['applies_to_stock_type'],
                'fields' => $row['fields'],
                'sort_order' => $row['sort_order'],
                'is_active' => true,
            ]);
        }
    }

    public static function ensureDefaultsIfNeeded(): void
    {
        if (auth()->user() === null) {
            return;
        }

        static::seedDefaultsIfEmpty();
    }
}
