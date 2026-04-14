<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryWebController extends Controller
{
    public function index(): View
    {
        $items = InventoryItem::query()
            ->with(['formTemplate', 'supplier'])
            ->orderBy('stock_type')
            ->orderBy('name')
            ->paginate(20);

        return view('modules.inventory.index', compact('items'));
    }

    public function create(): View
    {
        return view('modules.inventory.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedSettings($request);

        InventoryItem::query()->create($data);

        return redirect()->route('inventory.index')
            ->with('status', 'Article enregistré. Utilisez « Paramétrer » puis « Actualiser » pour le stock.');
    }

    public function parameterizeForm(InventoryItem $item): View
    {
        $schemaLinesForJs = InventoryItem::schemaLinesForParameterizeForm(old('schema_lines'), $item);
        $suppliers = Supplier::query()->orderBy('name')->get();

        return view('modules.inventory.parameterize', compact('item', 'schemaLinesForJs', 'suppliers'));
    }

    public function parameterizeUpdate(Request $request, InventoryItem $item): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'unit' => ['required', 'string', 'max:32'],
            'reorder_level' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'schema_lines' => ['nullable', 'array'],
            'schema_lines.*.key' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9_]*$/'],
            'schema_lines.*.label' => ['nullable', 'string', 'max:120'],
            'schema_lines.*.type' => ['nullable', 'in:number,text'],
        ]);

        $settings = [
            'name' => $request->input('name'),
            'unit' => $request->input('unit'),
            'reorder_level' => $request->input('reorder_level'),
            'notes' => ($n = $request->input('notes')) !== null && $n !== '' ? $n : null,
            'supplier_id' => $request->filled('supplier_id') ? (int) $request->input('supplier_id') : null,
        ];

        $lines = InventoryItem::buildSchemaFromParameterizeRequest(
            $request->input('schema_lines', []),
            $item
        );

        $item->update(array_merge($settings, [
            'inventory_form_template_id' => null,
            'characteristic_values' => $lines === [] ? null : $lines,
        ]));

        return redirect()->route('inventory.index')
            ->with('status', 'Paramétrage enregistré pour « '.$item->name.' ». Vous pouvez « Actualiser » le stock.');
    }

    public function refreshForm(InventoryItem $item): View
    {
        $schemaRows = $item->characteristicRowsForActualiser();

        return view('modules.inventory.refresh', compact('item', 'schemaRows'));
    }

    public function refreshUpdate(Request $request, InventoryItem $item): RedirectResponse
    {
        $base = $item->characteristicRowsForActualiser();
        if ($base === []) {
            return redirect()->route('inventory.parameterize', $item)
                ->with('status', 'Définissez d’abord les intitulés des caractéristiques (Paramétrer).');
        }

        $stockByNumericLines = $item->hasNumericCharacteristicRows();

        $rules = [
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:5000'],
        ];

        if (! $stockByNumericLines) {
            $rules['quantity_on_hand'] = ['required', 'numeric', 'min:0'];
        }

        $data = $request->validate($rules);
        $valsInput = $data['values'] ?? [];

        $newLines = [];
        foreach ($base as $row) {
            $k = $row['key'];
            $newLines[] = [
                'key' => $k,
                'label' => $row['label'],
                'type' => $row['type'],
                'value' => trim((string) ($valsInput[$k] ?? '')),
            ];
        }

        $payload = [
            'inventory_form_template_id' => null,
            'characteristic_values' => $newLines,
        ];

        if ($stockByNumericLines) {
            $payload['quantity_on_hand'] = InventoryItem::sumNumericCharacteristicValues($newLines);
        } else {
            $payload['quantity_on_hand'] = $data['quantity_on_hand'];
        }

        $item->update($payload);

        return redirect()->route('inventory.index')->with('status', 'Stock actualisé pour « '.$item->name.' ».');
    }

    public function destroy(InventoryItem $item): RedirectResponse
    {
        $item->delete();

        return redirect()->route('inventory.index')->with('status', 'Article supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedSettings(Request $request, ?InventoryItem $existing = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'unit' => ['required', 'string', 'max:32'],
            'reorder_level' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['notes'] = isset($data['notes']) && (string) $data['notes'] !== '' ? $data['notes'] : null;
        $data['stock_type'] = $existing?->stock_type ?? 'other';
        $data['sku'] = $existing?->sku;

        if ($existing === null) {
            $data['quantity_on_hand'] = 0;
            $data['inventory_form_template_id'] = null;
            $data['characteristic_values'] = null;
        }

        return $data;
    }
}
