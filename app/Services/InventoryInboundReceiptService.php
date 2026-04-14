<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryInboundReceiptService
{
    /**
     * @param  list<array{inventory_item_id: int, quantity: float, characteristic_key?: string|null}>  $lines
     */
    public function apply(int $supplierId, ?string $reference, array $lines): void
    {
        DB::transaction(function () use ($supplierId, $reference, $lines): void {
            foreach ($lines as $line) {
                $item = InventoryItem::query()->findOrFail((int) $line['inventory_item_id']);
                $delta = (float) $line['quantity'];
                $key = isset($line['characteristic_key']) ? (string) $line['characteristic_key'] : null;
                if ($key === '') {
                    $key = null;
                }

                $result = $item->applyInboundReceiptLine($delta, $key);

                StockMovement::query()->create([
                    'supplier_id' => $supplierId,
                    'inventory_item_id' => $item->id,
                    'quantity_delta' => $result['quantity_delta'],
                    'reason' => 'Réception fournisseur',
                    'reference' => $reference !== null && $reference !== '' ? $reference : null,
                ]);
            }
        });
    }
}
