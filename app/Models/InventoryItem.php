<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class InventoryItem extends Model
{
    protected $fillable = [
        'supplier_id',
        'inventory_form_template_id',
        'stock_type',
        'name',
        'description',
        'quality_label',
        'colors',
        'characteristic_values',
        'sku',
        'unit',
        'quantity_on_hand',
        'reorder_level',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:3',
            'reorder_level' => 'decimal:3',
            'colors' => 'array',
            'characteristic_values' => 'array',
        ];
    }

    public function formTemplate(): BelongsTo
    {
        return $this->belongsTo(InventoryFormTemplate::class, 'inventory_form_template_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'inventory_item_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * True si l’article a au moins une ligne caractéristique de type nombre (stock réparti par type / détail).
     */
    public function hasNumericCharacteristicRows(): bool
    {
        foreach ($this->characteristicRowsForActualiser() as $row) {
            if (($row['type'] ?? '') === 'number') {
                return true;
            }
        }

        return false;
    }

    /**
     * Ajoute une entrée de stock : article simple (delta sur quantité totale) ou avec lignes nombre (delta sur une ligne).
     *
     * @return array{quantity_delta: float, new_quantity_on_hand: float}
     */
    public function applyInboundReceiptLine(float $delta, ?string $numericCharacteristicKey): array
    {
        if ($delta <= 0) {
            throw new \InvalidArgumentException('La quantité doit être positive.');
        }

        $perLine = $this->hasNumericCharacteristicRows();

        if (! $perLine) {
            $newQty = round((float) $this->quantity_on_hand + $delta, 3);
            $this->quantity_on_hand = $newQty;
            $this->save();

            return ['quantity_delta' => $delta, 'new_quantity_on_hand' => $newQty];
        }

        if ($numericCharacteristicKey === null || $numericCharacteristicKey === '') {
            throw new \InvalidArgumentException('Choisissez une ligne (type / détail) pour cet article paramétré avec des champs nombre.');
        }

        $rows = $this->characteristicRowsForActualiser();
        if ($rows === []) {
            throw new \InvalidArgumentException('Paramétrez d’abord les caractéristiques de cet article.');
        }

        $found = false;
        foreach ($rows as $i => $row) {
            if (($row['key'] ?? '') !== $numericCharacteristicKey) {
                continue;
            }
            if (($row['type'] ?? '') !== 'number') {
                throw new \InvalidArgumentException('La ligne choisie doit être de type nombre.');
            }
            $current = 0.0;
            $raw = trim((string) ($row['value'] ?? ''));
            if ($raw !== '') {
                $normalized = str_replace(',', '.', $raw);
                if (is_numeric($normalized)) {
                    $current = (float) $normalized;
                }
            }
            $rows[$i]['value'] = (string) round($current + $delta, 3);
            $found = true;
            break;
        }

        if (! $found) {
            throw new \InvalidArgumentException('Ligne de caractéristique invalide pour cet article.');
        }

        $this->characteristic_values = $rows;
        $newTotal = self::sumNumericCharacteristicValues($rows);
        $this->quantity_on_hand = $newTotal;
        $this->save();

        return ['quantity_delta' => $delta, 'new_quantity_on_hand' => $newTotal];
    }

    /**
     * Retire une quantité sur une ligne « nombre » et recalcule le total (stock = somme des champs nombre).
     *
     * @return array{quantity_delta: float, new_quantity_on_hand: float}
     */
    public function applyOutboundFabricMeters(float $meters, string $numericCharacteristicKey): array
    {
        if ($meters <= 0) {
            throw new \InvalidArgumentException('La quantité à retirer doit être positive.');
        }

        if (! $this->hasNumericCharacteristicRows()) {
            throw new \InvalidArgumentException('Utilisez la déduction simple pour cet article sans champs nombre paramétrés.');
        }

        if ($numericCharacteristicKey === '') {
            throw new \InvalidArgumentException('Choisissez une ligne (type / détail) pour cet article.');
        }

        $rows = $this->characteristicRowsForActualiser();
        if ($rows === []) {
            throw new \InvalidArgumentException('Paramétrez d’abord les caractéristiques de cet article.');
        }

        $found = false;
        foreach ($rows as $i => $row) {
            if (($row['key'] ?? '') !== $numericCharacteristicKey) {
                continue;
            }
            if (($row['type'] ?? '') !== 'number') {
                throw new \InvalidArgumentException('La ligne choisie doit être de type nombre.');
            }
            $current = 0.0;
            $raw = trim((string) ($row['value'] ?? ''));
            if ($raw !== '') {
                $normalized = str_replace(',', '.', $raw);
                if (is_numeric($normalized)) {
                    $current = (float) $normalized;
                }
            }
            if ($current < $meters) {
                throw new \InvalidArgumentException('Stock insuffisant sur cette couleur / ligne.');
            }
            $rows[$i]['value'] = (string) round(max(0, $current - $meters), 3);
            $found = true;
            break;
        }

        if (! $found) {
            throw new \InvalidArgumentException('Ligne de caractéristique invalide pour cet article.');
        }

        $this->characteristic_values = $rows;
        $newTotal = self::sumNumericCharacteristicValues($rows);
        $this->quantity_on_hand = $newTotal;
        $this->save();

        return ['quantity_delta' => -$meters, 'new_quantity_on_hand' => $newTotal];
    }

    /**
     * Lignes pour le formulaire (saisie ou édition), avec prise en charge de l’ancien format clé/valeur ou modèle.
     *
     * @param  array<int, array<string, mixed>>|null  $oldLines
     * @return list<array{label: string, type: string, value: string}>
     */
    public static function characteristicLinesForEditor(?array $oldLines, ?self $item): array
    {
        if (is_array($oldLines) && $oldLines !== []) {
            return self::normalizeCharacteristicLinesFromRequest($oldLines);
        }
        if ($item === null) {
            return [];
        }

        return $item->characteristicValuesToLines();
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $raw
     * @return list<array{label: string, type: string, value: string}>
     */
    public static function normalizeCharacteristicLinesFromRequest(?array $raw): array
    {
        if ($raw === null || $raw === []) {
            return [];
        }
        $out = [];
        foreach (array_values($raw) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $type = ($row['type'] ?? '') === 'number' ? 'number' : 'text';
            $value = trim((string) ($row['value'] ?? ''));
            $key = trim((string) ($row['key'] ?? ''));
            $line = ['label' => $label, 'type' => $type, 'value' => $value];
            if ($key !== '') {
                $line['key'] = $key;
            }
            $out[] = $line;
        }

        return $out;
    }

    /**
     * @param  list<mixed>  $vals
     * @return list<array{key: string, label: string, type: string, value: string}>
     */
    public static function normalizeListCharacteristicRows(array $vals): array
    {
        $usedKeys = [];
        $out = [];
        foreach ($vals as $row) {
            if (! is_array($row)) {
                continue;
            }
            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $type = ($row['type'] ?? '') === 'number' ? 'number' : 'text';
            $key = trim((string) ($row['key'] ?? ''));
            if ($key === '' || ! preg_match('/^[a-z0-9_]+$/', $key)) {
                $slug = Str::slug($label, '_');
                $key = self::uniqueCharacteristicKey($slug !== '' ? $slug : 'champ', $usedKeys);
            } elseif (in_array($key, $usedKeys, true)) {
                $key = self::uniqueCharacteristicKey($key, $usedKeys);
            }
            $usedKeys[] = $key;
            $out[] = [
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'value' => trim((string) ($row['value'] ?? '')),
            ];
        }

        return $out;
    }

    /**
     * @param  list<string>  $usedKeys
     */
    public static function uniqueCharacteristicKey(string $base, array $usedKeys): string
    {
        $base = substr(Str::slug($base, '_'), 0, 60);
        if ($base === '') {
            $base = 'champ';
        }
        $candidate = $base;
        $n = 1;
        while (in_array($candidate, $usedKeys, true)) {
            $suffix = '_'.$n++;
            $candidate = substr($base, 0, max(1, 64 - strlen($suffix))).$suffix;
        }

        return $candidate;
    }

    /**
     * @return list<array{key: string, label: string, type: string, value: string}>
     */
    public function characteristicRowsForActualiser(): array
    {
        $vals = $this->characteristic_values;
        if (! is_array($vals) || ! array_is_list($vals)) {
            return [];
        }

        return self::normalizeListCharacteristicRows($vals);
    }

    /**
     * @return list<array{key: string, label: string, type: string}>
     */
    public static function schemaLinesForParameterizeForm(?array $oldLines, self $item): array
    {
        if (is_array($oldLines) && $oldLines !== []) {
            $out = [];
            foreach (array_values($oldLines) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $label = trim((string) ($row['label'] ?? ''));
                if ($label === '') {
                    continue;
                }
                $out[] = [
                    'key' => trim((string) ($row['key'] ?? '')),
                    'label' => $label,
                    'type' => ($row['type'] ?? '') === 'number' ? 'number' : 'text',
                ];
            }

            return $out;
        }

        return array_map(fn (array $r) => [
            'key' => $r['key'],
            'label' => $r['label'],
            'type' => $r['type'],
        ], $item->characteristicRowsForActualiser());
    }

    /**
     * @param  array<int, array<string, mixed>>  $raw
     * @return list<array{key: string, label: string, type: string, value: string}>
     */
    public static function buildSchemaFromParameterizeRequest(array $raw, InventoryItem $item): array
    {
        $valueByKey = [];
        foreach ($item->characteristicRowsForActualiser() as $row) {
            $valueByKey[$row['key']] = $row['value'];
        }

        $out = [];
        $usedKeys = [];
        foreach (array_values($raw) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $type = ($row['type'] ?? '') === 'number' ? 'number' : 'text';
            $incomingKey = trim((string) ($row['key'] ?? ''));
            if ($incomingKey !== '' && preg_match('/^[a-z0-9_]+$/', $incomingKey)) {
                $key = $incomingKey;
            } else {
                $slug = Str::slug($label, '_');
                $key = self::uniqueCharacteristicKey($slug !== '' ? $slug : 'champ', $usedKeys);
            }
            if (in_array($key, $usedKeys, true)) {
                $key = self::uniqueCharacteristicKey($key, $usedKeys);
            }
            $usedKeys[] = $key;
            $value = array_key_exists($key, $valueByKey) ? $valueByKey[$key] : '';
            $out[] = ['key' => $key, 'label' => $label, 'type' => $type, 'value' => $value];
        }

        return $out;
    }

    /**
     * Articles type « tissu léger / legé » : le stock en mètres = somme des champs personnalisés de type nombre.
     */
    public static function nameImpliesFabricMeterSum(string $name): bool
    {
        $n = mb_strtolower(trim($name));
        if ($n === '' || ! str_contains($n, 'tissu')) {
            return false;
        }

        return str_contains($n, 'leg')
            || str_contains($n, 'lé')
            || str_contains($n, 'lege')
            || str_contains($n, 'léger')
            || str_contains($n, 'leger');
    }

    /**
     * Somme des valeurs des lignes au type « number » (mètres par couleur, etc.).
     *
     * @param  list<array{label: string, type: string, value: string}>  $lines
     */
    public static function sumNumericCharacteristicValues(array $lines): float
    {
        $sum = 0.0;
        foreach ($lines as $line) {
            if (($line['type'] ?? '') !== 'number') {
                continue;
            }
            $raw = trim((string) ($line['value'] ?? ''));
            if ($raw === '') {
                continue;
            }
            $normalized = str_replace(',', '.', $raw);
            if (is_numeric($normalized)) {
                $sum += (float) $normalized;
            }
        }

        return round($sum, 3);
    }

    /**
     * @return list<array{key: string, label: string, type: string, value: string}>
     */
    public function characteristicValuesToLines(): array
    {
        $vals = $this->characteristic_values;
        if (! is_array($vals) || $vals === []) {
            return [];
        }

        if (array_is_list($vals)) {
            return self::normalizeListCharacteristicRows($vals);
        }

        $this->loadMissing('formTemplate');
        $tpl = $this->formTemplate;
        if ($tpl !== null) {
            $lines = [];
            foreach ($tpl->normalizedFields() as $f) {
                $k = $f['key'];
                if (! array_key_exists($k, $vals)) {
                    continue;
                }
                $v = trim((string) $vals[$k]);
                if ($v === '') {
                    continue;
                }
                $lines[] = [
                    'key' => (string) $k,
                    'label' => $f['label'],
                    'type' => $f['type'],
                    'value' => $v,
                ];
            }

            return $lines;
        }

        $usedKeys = [];
        $lines = [];
        foreach ($vals as $k => $v) {
            if (! is_string($v) && ! is_numeric($v)) {
                continue;
            }
            $s = trim((string) $v);
            if ($s === '') {
                continue;
            }
            $label = (string) $k;
            $slug = Str::slug($label, '_');
            $key = self::uniqueCharacteristicKey($slug !== '' ? $slug : 'champ', $usedKeys);
            $usedKeys[] = $key;
            $lines[] = ['key' => $key, 'label' => $label, 'type' => 'text', 'value' => $s];
        }

        return $lines;
    }

    /** @return list<string> */
    public function colorsList(): array
    {
        $c = $this->colors;

        if (! is_array($c)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $c), fn (string $s) => $s !== ''));
    }

    public function colorsSummary(): string
    {
        $list = $this->colorsList();

        return $list === [] ? '—' : implode(', ', $list);
    }

    public function stockTypeLabel(): string
    {
        return match ($this->stock_type) {
            'fabric' => 'Tissu',
            'accessory' => 'Accessoire',
            default => 'Autre',
        };
    }

    public function stockTypeBadgeClass(): string
    {
        return match ($this->stock_type) {
            'fabric' => 'bg-emerald-100 text-emerald-800',
            'accessory' => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /** Affichage liste stock : entiers uniquement (pas de décimales à l’écran). */
    public static function formatStockForList(float|int|string $value): string
    {
        return (string) (int) round((float) $value);
    }

    public function characteristicsSummary(): string
    {
        $vals = $this->characteristic_values;
        if (is_array($vals) && $vals !== []) {
            if (array_is_list($vals) && isset($vals[0]) && is_array($vals[0]) && array_key_exists('label', $vals[0])) {
                $parts = [];
                foreach ($vals as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $l = trim((string) ($row['label'] ?? ''));
                    $v = trim((string) ($row['value'] ?? ''));
                    if ($l === '' && $v === '') {
                        continue;
                    }
                    if ($v === '') {
                        continue;
                    }
                    $parts[] = ($l !== '' ? $l.': ' : '').$v;
                }

                return $parts !== [] ? implode(' · ', $parts) : '—';
            }

            $this->loadMissing('formTemplate');
            $tpl = $this->formTemplate;
            if ($tpl !== null) {
                $parts = [];
                foreach ($tpl->normalizedFields() as $f) {
                    $k = $f['key'];
                    if (! isset($vals[$k]) || trim((string) $vals[$k]) === '') {
                        continue;
                    }
                    $u = $f['unit'] !== '' ? ' '.$f['unit'] : '';
                    $parts[] = $f['label'].': '.(string) $vals[$k].$u;
                }

                return $parts !== [] ? implode(' · ', $parts) : '—';
            }

            $fallback = collect($vals)
                ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                ->map(fn (string $v, string $k) => $k.': '.$v)
                ->values()
                ->all();

            return $fallback !== [] ? implode(' · ', $fallback) : '—';
        }

        $legacy = [];
        if (filled($this->quality_label)) {
            $legacy[] = $this->quality_label;
        }
        if ($this->colorsList() !== []) {
            $legacy[] = 'Couleurs : '.$this->colorsSummary();
        }
        if (filled($this->description)) {
            $legacy[] = $this->description;
        }

        return $legacy !== [] ? implode(' · ', $legacy) : '—';
    }
}
