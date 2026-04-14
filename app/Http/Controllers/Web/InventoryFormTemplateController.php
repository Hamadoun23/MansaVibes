<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InventoryFormTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryFormTemplateController extends Controller
{
    public function index(): View
    {
        $templates = InventoryFormTemplate::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('modules.inventory-form-templates.index', compact('templates'));
    }

    public function create(Request $request): View
    {
        $existingTemplates = InventoryFormTemplate::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $copyFromRaw = $request->query('from');
        $copySource = null;
        if ($copyFromRaw !== null && $copyFromRaw !== '') {
            $copySource = InventoryFormTemplate::query()
                ->whereKey((int) $copyFromRaw)
                ->first();
        }
        $copyFromId = $copySource !== null ? (string) $copySource->id : null;

        $templatesCatalog = $existingTemplates->map(static function (InventoryFormTemplate $t) {
            return [
                'id' => (string) $t->id,
                'name' => $t->name,
                'fields' => $t->normalizedFields(),
                'applies_to_stock_type' => $t->applies_to_stock_type,
                'notes' => (string) ($t->notes ?? ''),
                'sort_order' => (int) $t->sort_order,
                'is_active' => (bool) $t->is_active,
            ];
        })->values()->all();

        return view('modules.inventory-form-templates.create', compact(
            'existingTemplates',
            'copySource',
            'copyFromId',
            'templatesCatalog'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedTemplate($request);

        InventoryFormTemplate::query()->create($data);

        return redirect()->route('inventory-form-templates.index')
            ->with('status', 'Modèle de fiche stock créé.');
    }

    public function edit(InventoryFormTemplate $inventory_form_template): View
    {
        return view('modules.inventory-form-templates.edit', ['template' => $inventory_form_template]);
    }

    public function update(Request $request, InventoryFormTemplate $inventory_form_template): RedirectResponse
    {
        $data = $this->validatedTemplate($request);

        $inventory_form_template->update($data);

        return redirect()->route('inventory-form-templates.index')
            ->with('status', 'Modèle mis à jour.');
    }

    public function destroy(InventoryFormTemplate $inventory_form_template): RedirectResponse
    {
        $inventory_form_template->delete();

        return redirect()->route('inventory-form-templates.index')
            ->with('status', 'Modèle supprimé. Les articles conservent leurs valeurs ; la fiche n’est plus liée au modèle.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedTemplate(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'applies_to_stock_type' => ['nullable', 'in:fabric,accessory,other'],
            'notes' => ['nullable', 'string'],
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
                        $slug = 'champ';
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
                'fields' => 'Ajoutez au moins un champ avec un intitulé rempli.',
            ]);
        }

        $stockTypeRaw = $validated['applies_to_stock_type'] ?? null;
        $appliesTo = is_string($stockTypeRaw) && $stockTypeRaw !== '' ? $stockTypeRaw : null;

        return [
            'name' => $validated['name'],
            'applies_to_stock_type' => $appliesTo,
            'notes' => $validated['notes'] ?? null,
            'fields' => $fields,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
