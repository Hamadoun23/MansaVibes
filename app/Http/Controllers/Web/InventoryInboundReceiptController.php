<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Supplier;
use App\Services\InventoryInboundReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class InventoryInboundReceiptController extends Controller
{
    public function create(Request $request): View
    {
        $suppliers = Supplier::query()->orderBy('name')->get();

        $preselectedSupplierId = null;
        $q = $request->query('supplier');
        if ($q !== null && ctype_digit((string) $q)) {
            $s = Supplier::query()->find((int) $q);
            if ($s !== null) {
                $preselectedSupplierId = $s->id;
            }
        }

        $inventoryItems = InventoryItem::query()->orderBy('name')->get();

        $itemsPayload = $inventoryItems
            ->map(function (InventoryItem $i) {
                $numericRows = collect($i->characteristicRowsForActualiser())
                    ->filter(fn ($r) => ($r['type'] ?? '') === 'number')
                    ->values()
                    ->map(fn ($r) => ['key' => $r['key'], 'label' => $r['label']])
                    ->all();

                return [
                    'id' => $i->id,
                    'name' => $i->name,
                    'unit' => $i->unit,
                    'numericRows' => $numericRows,
                ];
            })
            ->values()
            ->all();

        return view('modules.inventory.reception', compact('suppliers', 'preselectedSupplierId', 'itemsPayload', 'inventoryItems'));
    }

    public function store(Request $request, InventoryInboundReceiptService $service): RedirectResponse
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'reference' => ['nullable', 'string', 'max:120'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'lines.*.allocations' => ['nullable', 'array'],
            'lines.*.allocations.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $lines = $this->expandReceptionLines($request->input('lines', []));

        $service->apply(
            (int) $request->input('supplier_id'),
            $request->input('reference') ?: null,
            $lines
        );

        return redirect()->route('inventory.index')->with('status', 'Réception enregistrée.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $rawLines
     * @return list<array{inventory_item_id: int, quantity: float, characteristic_key?: string|null}>
     */
    protected function expandReceptionLines(array $rawLines): array
    {
        $out = [];

        foreach ($rawLines as $line) {
            if (! is_array($line)) {
                continue;
            }

            $item = InventoryItem::query()->findOrFail((int) ($line['inventory_item_id'] ?? 0));
            $perLine = $item->hasNumericCharacteristicRows();

            if ($perLine) {
                $allowedKeys = collect($item->characteristicRowsForActualiser())
                    ->filter(fn ($r) => ($r['type'] ?? '') === 'number')
                    ->pluck('key')
                    ->map(fn ($k) => (string) $k)
                    ->all();

                if ($allowedKeys === []) {
                    throw ValidationException::withMessages([
                        'lines' => 'L’article « '.$item->name.' » n’a pas de champ nombre : paramétrez-le d’abord (Paramétrer l’article).',
                    ]);
                }

                $allocations = is_array($line['allocations'] ?? null) ? $line['allocations'] : [];
                $any = false;
                foreach ($allowedKeys as $k) {
                    $raw = $allocations[$k] ?? null;
                    if ($raw === null || $raw === '') {
                        continue;
                    }
                    $qty = (float) str_replace(',', '.', (string) $raw);
                    if ($qty <= 0) {
                        continue;
                    }
                    $any = true;
                    $out[] = [
                        'inventory_item_id' => $item->id,
                        'quantity' => $qty,
                        'characteristic_key' => $k,
                    ];
                }

                if (! $any) {
                    throw ValidationException::withMessages([
                        'lines' => 'Pour « '.$item->name.' », indiquez au moins une quantité pour un type / détail (champs nombre).',
                    ]);
                }

                continue;
            }

            $qty = (float) ($line['quantity'] ?? 0);
            if ($qty <= 0) {
                throw ValidationException::withMessages([
                    'lines' => 'Indiquez une quantité reçue pour « '.$item->name.' ».',
                ]);
            }

            $out[] = [
                'inventory_item_id' => $item->id,
                'quantity' => $qty,
                'characteristic_key' => null,
            ];
        }

        if ($out === []) {
            throw ValidationException::withMessages([
                'lines' => 'Ajoutez au moins une ligne de réception valide.',
            ]);
        }

        return $out;
    }
}
