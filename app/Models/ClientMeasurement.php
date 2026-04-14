<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientMeasurement extends Model
{
    protected $fillable = [
        'client_id',
        'measurement_form_template_id',
        'label',
        'data',
        'poitrine_cm',
        'taille_cm',
        'hanche_cm',
        'longueur_cm',
        'epaule_cm',
        'custom_measures',
        'measurement_notes',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'custom_measures' => 'array',
            'poitrine_cm' => 'decimal:2',
            'taille_cm' => 'decimal:2',
            'hanche_cm' => 'decimal:2',
            'longueur_cm' => 'decimal:2',
            'epaule_cm' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function measurementTemplate(): BelongsTo
    {
        return $this->belongsTo(MeasurementFormTemplate::class, 'measurement_form_template_id');
    }

    public function isTemplateBased(): bool
    {
        return $this->measurement_form_template_id !== null;
    }

    /**
     * Lignes à afficher (modèle configuré ou ancien format).
     *
     * @return list<array{label: string, value: string, unit: string}>
     */
    public function displayFieldRows(): array
    {
        $template = $this->relationLoaded('measurementTemplate')
            ? $this->measurementTemplate
            : $this->measurementTemplate()->first();

        if ($template !== null) {
            $data = is_array($this->data) ? $this->data : [];
            $rows = [];
            foreach ($template->normalizedFields() as $f) {
                $k = $f['key'];
                if (! array_key_exists($k, $data)) {
                    continue;
                }
                $val = $data[$k];
                if ($val === null || $val === '') {
                    continue;
                }
                $rows[] = [
                    'label' => $f['label'],
                    'value' => is_scalar($val) ? (string) $val : '',
                    'unit' => $f['unit'],
                ];
            }

            return $rows;
        }

        $rows = [];
        foreach (['poitrine_cm' => 'Poitrine', 'taille_cm' => 'Taille', 'hanche_cm' => 'Hanche', 'longueur_cm' => 'Longueur', 'epaule_cm' => 'Épaule'] as $attr => $lbl) {
            $v = $this->{$attr};
            if ($v !== null && $v !== '') {
                $rows[] = ['label' => $lbl, 'value' => (string) $v, 'unit' => 'cm'];
            }
        }

        return $rows;
    }

    /**
     * @return list<array{label: string, value: string, unit?: string}>
     */
    public function normalizedCustomMeasures(): array
    {
        $raw = $this->custom_measures ?? [];

        return collect($raw)->map(function ($row) {
            if (is_string($row)) {
                return ['label' => 'Mesure', 'value' => $row, 'unit' => ''];
            }

            return [
                'label' => (string) ($row['label'] ?? ''),
                'value' => (string) ($row['value'] ?? ''),
                'unit' => (string) ($row['unit'] ?? ''),
            ];
        })->values()->all();
    }
}
