<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderInventoryDeductionService
{
    /**
     * Déduit le stock pour les lignes liées à l’inventaire : articles simples (quantité) ou avec champs nombre (quantité sur une ligne de détail).
     * Une seule fois par commande (inventory_deducted_at).
     *
     * @return list<string> messages de lignes ignorées
     */
    public function deduct(Order $order): array
    {
        if ($order->inventory_deducted_at !== null) {
            throw ValidationException::withMessages([
                'inventory' => 'Le stock a déjà été déduit pour cette commande.',
            ]);
        }

        $order->load('items');

        $skipped = [];
        $toApply = [];

        foreach ($order->items as $line) {
            if ($line->client_supplies_fabric) {
                $skipped[] = 'Ligne « '.$line->description.' » : tissu client — pas de déduction atelier.';

                continue;
            }
            if ($line->inventory_item_id === null) {
                continue;
            }

            $item = InventoryItem::query()->find($line->inventory_item_id);
            if ($item === null) {
                $skipped[] = 'Ligne « '.$line->description.' » : article stock introuvable.';

                continue;
            }

            if ($item->hasNumericCharacteristicRows()) {
                $key = trim((string) ($line->inventory_characteristic_key ?? ''));
                $meters = (float) ($line->inventory_consumed_meters ?? 0);
                if ($key === '' || $meters <= 0) {
                    $skipped[] = 'Ligne « '.$line->description.' » : pour « '.$item->name.' », renseignez la ligne de stock et la quantité à prélever.';

                    continue;
                }

                $toApply[] = [
                    'kind' => 'fabric',
                    'line' => $line,
                    'item_id' => $item->id,
                    'key' => $key,
                    'meters' => $meters,
                ];

                continue;
            }

            $qty = (float) $line->quantity;
            if ($qty <= 0) {
                continue;
            }

            if ((float) $item->quantity_on_hand < $qty) {
                throw ValidationException::withMessages([
                    'inventory' => 'Stock insuffisant pour « '.$item->name.' » (disponible : '.InventoryItem::formatStockForList($item->quantity_on_hand).' '.$item->unit.', demandé : '.InventoryItem::formatStockForList($qty).').',
                ]);
            }

            $toApply[] = [
                'kind' => 'simple',
                'line' => $line,
                'item_id' => $item->id,
                'qty' => $qty,
            ];
        }

        if ($toApply === []) {
            if ($skipped !== []) {
                throw ValidationException::withMessages([
                    'inventory' => 'Aucune ligne éligible à la déduction. '.implode(' ', $skipped),
                ]);
            }

            throw ValidationException::withMessages([
                'inventory' => 'Aucune ligne liée à un article de stock (sans tissu client). Indiquez l’article et, si l’article a des champs nombre, la ligne et la quantité prélevée.',
            ]);
        }

        DB::transaction(function () use ($order, $toApply): void {
            foreach ($toApply as $row) {
                if ($row['kind'] === 'fabric') {
                    $inv = InventoryItem::query()->findOrFail($row['item_id']);
                    try {
                        $result = $inv->applyOutboundFabricMeters($row['meters'], $row['key']);
                    } catch (\InvalidArgumentException $e) {
                        throw ValidationException::withMessages([
                            'inventory' => $e->getMessage(),
                        ]);
                    }

                    StockMovement::query()->create([
                        'tenant_id' => $order->tenant_id,
                        'supplier_id' => null,
                        'inventory_item_id' => $inv->id,
                        'quantity_delta' => $result['quantity_delta'],
                        'reason' => 'Commande '.($order->reference ?? '#'.$order->id),
                        'reference' => 'order:'.$order->id,
                    ]);
                } else {
                    $inv = InventoryItem::query()->findOrFail($row['item_id']);
                    $qty = $row['qty'];
                    $inv->refresh();
                    $newQty = round(max(0, (float) $inv->quantity_on_hand - $qty), 3);
                    $inv->quantity_on_hand = $newQty;
                    $inv->save();

                    StockMovement::query()->create([
                        'tenant_id' => $order->tenant_id,
                        'supplier_id' => null,
                        'inventory_item_id' => $inv->id,
                        'quantity_delta' => -$qty,
                        'reason' => 'Commande '.($order->reference ?? '#'.$order->id),
                        'reference' => 'order:'.$order->id,
                    ]);
                }
            }

            $order->inventory_deducted_at = now();
            $order->save();
        });

        return $skipped;
    }
}
