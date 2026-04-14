<?php

namespace Database\Seeders\Support;

use App\Models\CashMovement;
use App\Models\Client;
use App\Models\CommerceCartItem;
use App\Models\DailyCashClosure;
use App\Models\Employee;
use App\Models\FinanceCategory;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\MeasurementFormTemplate;
use App\Models\InvoiceItem;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\OrderImage;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\ReportingSnapshot;
use App\Models\StaffPerformance;
use App\Models\StaffTask;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\User;
use Faker\Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Données fictives pour tous les modules métier (hors clients).
 */
class TenantBusinessDemoGenerator
{
    public function __construct(private Generator $faker) {}

    /**
     * @param  Collection<int, Client>  $clients
     */
    public function generate(User $actor, Collection $clients): void
    {
        $faker = $this->faker;

        $employees = collect();
        foreach (['Maître tailleur', 'Styliste', 'Apprenti', 'Chef couturière', 'Brodeuse'] as $title) {
            $employees->push(Employee::query()->create([
                'name' => $faker->name(),
                'phone' => '+221 '.random_int(70, 79).' '.$faker->numerify('### ## ##'),
                'role_title' => $title,
            ]));
        }

        $catIn = FinanceCategory::query()->create(['name' => 'Ventes atelier', 'type' => 'income']);
        $catOut = FinanceCategory::query()->create(['name' => 'Achats tissus', 'type' => 'expense']);
        FinanceCategory::query()->create(['name' => 'Salaires & charges', 'type' => 'expense']);
        FinanceCategory::query()->create(['name' => 'Loyer & utilitaires', 'type' => 'expense']);

        foreach (range(1, 32) as $_) {
            $dir = $faker->randomElement(['in', 'out']);
            CashMovement::query()->create([
                'finance_category_id' => $faker->randomElement([$catIn->id, $catOut->id, null]),
                'direction' => $dir,
                'amount_cents' => $dir === 'in'
                    ? $faker->numberBetween(10_000, 180_000)
                    : $faker->numberBetween(5_000, 95_000),
                'label' => $faker->randomElement([
                    'Vente comptant', 'Achat wax Roumba', 'Mercerie Grand Marché', 'Acompte client',
                    'Transport fournisseur', 'Réparation surjeteuse', 'Dépôt banque',
                ]),
                'movement_date' => $faker->dateTimeBetween('-90 days', 'now'),
                'notes' => $faker->optional(0.2)->sentence(),
            ]);
        }

        DailyCashClosure::query()->create([
            'closed_on' => now()->subDay()->toDateString(),
            'opening_cents' => 125_000,
            'closing_cents' => 198_500,
            'notes' => 'Bonne affluence matinée.',
        ]);
        DailyCashClosure::query()->create([
            'closed_on' => now()->subDays(2)->toDateString(),
            'opening_cents' => 95_000,
            'closing_cents' => 142_000,
            'notes' => null,
        ]);

        $fabricStock = [
            ['name' => 'Wax Vlisco 6 yards', 'unit' => 'm'],
            ['name' => 'Bazin richesse damassé', 'unit' => 'm'],
            ['name' => 'Coton uni ivoire', 'unit' => 'm'],
            ['name' => 'Lin naturel', 'unit' => 'm'],
            ['name' => 'Voile léger noir', 'unit' => 'm'],
            ['name' => 'Satin doublure', 'unit' => 'm'],
        ];
        $accessoryStock = [
            ['name' => 'Fermeture invisible 22 cm', 'unit' => 'pièce'],
            ['name' => 'Boutons dorés 15 mm', 'unit' => 'pièce'],
            ['name' => 'Chaîne décorative or', 'unit' => 'm'],
            ['name' => 'Élastique maille 20 mm', 'unit' => 'm'],
            ['name' => 'Fil polyester 5000y', 'unit' => 'rouleau'],
            ['name' => 'Aiguilles machine 90', 'unit' => 'boîte'],
            ['name' => 'Ruban satin or', 'unit' => 'm'],
        ];
        $inventory = collect();
        foreach ($fabricStock as $row) {
            $inventory->push(InventoryItem::query()->create([
                'inventory_form_template_id' => null,
                'stock_type' => 'fabric',
                'name' => $row['name'],
                'sku' => 'MV-F-'.strtoupper(Str::random(4)),
                'unit' => $row['unit'],
                'quantity_on_hand' => $faker->randomFloat(3, 4, 120),
                'reorder_level' => 10,
                'notes' => $faker->optional(0.12)->sentence(),
                'characteristic_values' => [
                    ['label' => 'Couleurs', 'type' => 'text', 'value' => $faker->randomElement(['Indigo', 'Rouge brique, ivoire', 'Noir, or'])],
                    ['label' => 'Qualité', 'type' => 'text', 'value' => $faker->randomElement(['Premium', 'Standard atelier', 'Import'])],
                    ['label' => 'Largeur utile', 'type' => 'number', 'value' => (string) $faker->numberBetween(110, 150)],
                ],
            ]));
        }
        foreach ($accessoryStock as $row) {
            $inventory->push(InventoryItem::query()->create([
                'inventory_form_template_id' => null,
                'stock_type' => 'accessory',
                'name' => $row['name'],
                'sku' => 'MV-A-'.strtoupper(Str::random(4)),
                'unit' => $row['unit'],
                'quantity_on_hand' => $faker->randomFloat(3, 6, 240),
                'reorder_level' => 15,
                'notes' => $faker->optional(0.12)->sentence(),
                'characteristic_values' => [
                    ['label' => 'Taille / dimension', 'type' => 'text', 'value' => $faker->randomElement(['22 cm', '15 mm', '5000 m', '20 mm'])],
                    ['label' => 'Matériau', 'type' => 'text', 'value' => $faker->randomElement(['Laiton', 'Polyester', 'Acier', 'Soie synthétique'])],
                    ['label' => 'Réf. fournisseur', 'type' => 'text', 'value' => 'REF-'.$faker->bothify('??###')],
                ],
            ]));
        }
        foreach ($inventory->random(min(4, $inventory->count())) as $lowItem) {
            $lowItem->update(['quantity_on_hand' => max(1, (float) $lowItem->reorder_level - $faker->randomFloat(2, 1, 4))]);
        }

        foreach ($inventory->random(min(5, $inventory->count())) as $item) {
            StockAlert::query()->create([
                'inventory_item_id' => $item->id,
                'threshold' => 15,
                'active' => true,
            ]);
        }
        foreach (range(1, 18) as $_) {
            $item = $inventory->random();
            StockMovement::query()->create([
                'inventory_item_id' => $item->id,
                'quantity_delta' => $faker->randomFloat(3, -25, 40),
                'reason' => $faker->randomElement(['Réception fournisseur', 'Sortie commande', 'Inventaire', 'Perte']),
            ]);
        }

        $catalog = [
            'Robe de cérémonie wax', 'Ensemble trois pièces bazin', 'Chemise homme sur mesure', 'Pagne tailleur deux pièces',
            'Grand boubou brodé', 'Jupe crayon + chemisier', 'Tenue fillette baptême', 'Veste smoking',
        ];
        foreach ($catalog as $pname) {
            $product = Product::query()->create([
                'name' => $pname,
                'slug' => Str::slug($pname).'-'.Str::lower(Str::random(6)),
                'price_cents' => $faker->numberBetween(45_000, 320_000),
                'description' => $faker->paragraph(),
                'is_active' => $faker->boolean(90),
            ]);
            ProductImage::query()->create([
                'product_id' => $product->id,
                'path' => 'demo/placeholder-'.Str::random(4).'.jpg',
                'sort_order' => 0,
            ]);
        }

        $statuses = ['pending', 'in_progress', 'done', 'delivered'];
        $modelNames = [
            'Robe wax empire', 'Grand boubou brodé', 'Costume 3 pièces', 'Jupe crayon + top',
            'Chemise traditionnelle', 'Tenue baptême fillette', 'Ensemble pagne tailleur',
        ];
        $measurementTemplates = MeasurementFormTemplate::query()
            ->where('is_active', true)
            ->get();
        $tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true) ?: '';

        foreach (range(1, 22) as $i) {
            $client = $clients->random();
            $emp = $faker->boolean(78) ? $employees->random() : null;
            $tpl = $measurementTemplates->isNotEmpty() ? $measurementTemplates->random() : null;
            $order = Order::query()->create([
                'client_id' => $client->id,
                'reference' => 'CMD-'.now()->format('Y').'-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'measurement_form_template_id' => $tpl?->id,
                'model_name' => $tpl ? $tpl->name : $faker->randomElement($modelNames),
                'status' => $faker->randomElement($statuses),
                'due_date' => null,
                'delivery_mode' => $faker->randomElement(['pickup', 'delivery']),
                'assigned_to' => $emp?->id,
                'total_cents' => 0,
                'advance_payment_cents' => 0,
                'payment_method' => null,
                'discount_scope' => 'none',
                'order_discount_cents' => 0,
                'model_notes' => $faker->optional(0.25)->sentence(),
                'notes' => $faker->optional(0.35)->sentence(),
            ]);

            foreach (range(1, random_int(1, 4)) as $_) {
                $qty = random_int(1, 3);
                $unit = $faker->numberBetween(8_000, 95_000);
                $lineTpl = $measurementTemplates->isNotEmpty() && $faker->boolean(40)
                    ? $measurementTemplates->random()->id
                    : null;
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'measurement_form_template_id' => $lineTpl,
                    'description' => $faker->randomElement([
                        'Robe cocktail', 'Retouches pantalon', 'Nappes brodées', 'Costume 3 pièces',
                        'Voile complément', 'Haut pagne', 'Jupe longue', 'Chemise traditionnelle',
                    ]),
                    'quantity' => $qty,
                    'unit_price_cents' => $unit,
                    'discount_cents' => 0,
                    'discount_applies' => false,
                    'client_supplies_fabric' => false,
                ]);
            }

            $order->refresh();
            $order->recalculateTotals();
            $total = (int) $order->total_cents;

            $advance = 0;
            if ($total > 0) {
                $advance = $faker->numberBetween(0, $total);
                if ($faker->boolean(22)) {
                    $advance = $total;
                }
            }
            $payMethods = array_keys(Order::paymentMethodLabels());
            $order->update([
                'advance_payment_cents' => $advance,
                'payment_method' => $advance > 0 ? $faker->randomElement($payMethods) : null,
            ]);

            if ($tinyPng !== '' && $faker->boolean(72)) {
                foreach (range(0, random_int(0, 2)) as $imgIdx) {
                    $relPath = 'demo/orders/'.$order->id.'-'.$imgIdx.'-'.Str::random(4).'.png';
                    Storage::disk('public')->put($relPath, $tinyPng);
                    OrderImage::query()->create([
                        'order_id' => $order->id,
                        'path' => $relPath,
                        'caption' => $faker->optional(0.4)->randomElement(['Vue face', 'Détail broderie', 'Dos']),
                        'sort_order' => $imgIdx,
                    ]);
                }
            }

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'status' => 'pending',
                'user_id' => $actor->id,
            ]);
            if ($order->status !== 'pending') {
                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'status' => 'in_progress',
                    'user_id' => $actor->id,
                ]);
            }
            if (in_array($order->status, ['done', 'delivered'], true)) {
                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'user_id' => $actor->id,
                ]);
            }

            if ($emp && $faker->boolean(35)) {
                OrderAssignment::query()->create([
                    'order_id' => $order->id,
                    'employee_id' => $emp->id,
                    'role' => $faker->randomElement(['Coupe', 'Montage', 'Finitions']),
                ]);
            }
        }

        foreach (range(1, 6) as $n) {
            $qClient = $clients->random();
            $quote = Quote::query()->create([
                'client_id' => $qClient->id,
                'status' => $faker->randomElement(['draft', 'sent', 'accepted']),
                'total_cents' => 0,
                'valid_until' => now()->addDays(21 + $n),
            ]);
            $qt = 0;
            foreach (range(1, 2) as $__n) {
                $uq = random_int(1, 2);
                $up = $faker->numberBetween(25_000, 120_000);
                $qt += $uq * $up;
                QuoteItem::query()->create([
                    'quote_id' => $quote->id,
                    'description' => $faker->randomElement(['Ensemble complet', 'Robe + voile', 'Tenue famille']),
                    'quantity' => $uq,
                    'unit_price_cents' => $up,
                ]);
            }
            $quote->update(['total_cents' => $qt]);
        }

        $invBase = 200;
        foreach ($clients->random(min(12, $clients->count())) as $c) {
            $invBase++;
            $status = $faker->randomElement(['draft', 'sent', 'paid']);
            $invoice = Invoice::query()->create([
                'client_id' => $c->id,
                'number' => 'FAC-'.now()->year.'-'.str_pad((string) $invBase, 4, '0', STR_PAD_LEFT),
                'status' => $status,
                'total_cents' => 0,
            ]);
            $amount = $faker->numberBetween(28_000, 195_000);
            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'description' => $faker->randomElement(['Prestation couture', 'Ensemble sur mesure', 'Retouches + matière']),
                'quantity' => 1,
                'unit_price_cents' => $amount,
            ]);
            $invoice->update(['total_cents' => $amount]);
            if ($status === 'paid') {
                Payment::query()->create([
                    'invoice_id' => $invoice->id,
                    'amount_cents' => $amount,
                    'paid_at' => $faker->dateTimeBetween('-30 days', 'now'),
                    'method' => $faker->randomElement(['espèces', 'Orange Money', 'Wave', 'virement']),
                ]);
            }
        }

        foreach ($employees as $employee) {
            StaffTask::query()->create([
                'employee_id' => $employee->id,
                'title' => $faker->sentence(3),
                'status' => $faker->randomElement(['pending', 'in_progress', 'done']),
                'due_date' => $faker->dateTimeBetween('now', '+20 days'),
            ]);
            StaffPerformance::query()->updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'period_year' => (int) now()->year,
                    'period_month' => (int) now()->month,
                ],
                ['completed_tasks' => $faker->numberBetween(8, 48)]
            );
        }

        ReportingSnapshot::query()->create([
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'metrics' => [
                'orders_open' => 14,
                'orders_delivered_mtd' => 9,
                'revenue_cents' => 2_450_000,
                'expenses_cents' => 980_000,
            ],
        ]);

        foreach (range(1, 15) as $_) {
            NotificationLog::query()->create([
                'channel' => $faker->randomElement(['sms', 'whatsapp', 'email']),
                'recipient' => $faker->phoneNumber(),
                'body' => $faker->randomElement([
                    'Votre commande est prête au retrait.',
                    'Rappel : essayage demain 15h.',
                    'Devis expirant dans 3 jours.',
                ]),
                'status' => $faker->randomElement(['sent', 'pending', 'failed']),
                'meta' => ['template' => 'order_status'],
            ]);
        }

        $firstProduct = Product::query()->first();
        if ($firstProduct !== null) {
            CommerceCartItem::query()->create([
                'session_id' => 'demo-session-'.Str::random(8),
                'product_id' => $firstProduct->id,
                'quantity' => 1,
            ]);
        }
    }
}
