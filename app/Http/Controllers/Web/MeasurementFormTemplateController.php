<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MeasurementFormTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MeasurementFormTemplateController extends Controller
{
    public function index(): View
    {
        $templates = MeasurementFormTemplate::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('modules.measurement-templates.index', compact('templates'));
    }

    public function create(Request $request): View
    {
        $existingTemplates = MeasurementFormTemplate::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $copyFromRaw = $request->query('from');
        $copySource = null;
        if ($copyFromRaw !== null && $copyFromRaw !== '') {
            $copySource = MeasurementFormTemplate::query()
                ->whereKey((int) $copyFromRaw)
                ->first();
        }
        $copyFromId = $copySource !== null ? (string) $copySource->id : null;

        $templatesCatalog = $existingTemplates->map(static function (MeasurementFormTemplate $t) {
            return [
                'id' => (string) $t->id,
                'name' => $t->name,
                'fields' => $t->normalizedFields(),
                'reference_price_fcfa' => (int) $t->reference_price_fcfa,
                'notes' => (string) ($t->notes ?? ''),
                'sort_order' => (int) $t->sort_order,
                'is_active' => (bool) $t->is_active,
            ];
        })->values()->all();

        return view('modules.measurement-templates.create', compact(
            'existingTemplates',
            'copySource',
            'copyFromId',
            'templatesCatalog'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedTemplate($request);

        MeasurementFormTemplate::query()->create($data);

        return redirect()->route('measurement-templates.index')
            ->with('status', 'Modèle de formulaire créé.');
    }

    public function edit(MeasurementFormTemplate $measurement_template): View
    {
        return view('modules.measurement-templates.edit', ['template' => $measurement_template]);
    }

    public function update(Request $request, MeasurementFormTemplate $measurement_template): RedirectResponse
    {
        $data = $this->validatedTemplate($request);

        $measurement_template->update($data);

        return redirect()->route('measurement-templates.index')
            ->with('status', 'Modèle mis à jour.');
    }

    public function destroy(MeasurementFormTemplate $measurement_template): RedirectResponse
    {
        $measurement_template->delete();

        return redirect()->route('measurement-templates.index')
            ->with('status', 'Modèle supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedTemplate(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'reference_price_fcfa' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.key' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9_]*$/'],
            'fields.*.label' => ['nullable', 'string', 'max:120'],
            'fields.*.unit' => ['nullable', 'string', 'max:30'],
            'fields.*.type' => ['nullable', 'in:number,text'],
        ]);

        $usedKeys = [];
        $fields = collect($validated['fields'])
            ->map(function (array $row) use (&$usedKeys) {
                $label = trim((string) ($row['label'] ?? ''));
                if ($label === '') {
                    return null;
                }

                $type = ($row['type'] ?? '') === 'text' ? 'text' : 'number';
                $unit = trim((string) ($row['unit'] ?? ''));

                $incoming = strtolower((string) preg_replace('/[^a-z0-9_]/', '', (string) ($row['key'] ?? '')));

                if ($incoming !== '') {
                    $baseKey = substr($incoming, 0, 64);
                } else {
                    $slug = Str::slug($label, '_');
                    if ($slug === '') {
                        $slug = 'mesure';
                    }
                    $baseKey = substr($slug, 0, 50);
                }

                $candidate = $baseKey;
                $n = 1;
                while (in_array($candidate, $usedKeys, true)) {
                    $suffix = '_'.$n++;
                    $candidate = substr($baseKey, 0, 64 - strlen($suffix)).$suffix;
                }
                $usedKeys[] = $candidate;

                return [
                    'key' => $candidate,
                    'label' => $label,
                    'unit' => $unit,
                    'type' => $type,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($fields === []) {
            throw ValidationException::withMessages([
                'fields' => 'Ajoutez au moins une question avec un intitulé rempli.',
            ]);
        }

        return [
            'name' => $validated['name'],
            'notes' => $validated['notes'] ?? null,
            'reference_price_fcfa' => (int) ($validated['reference_price_fcfa'] ?? 0),
            'fields' => $fields,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
