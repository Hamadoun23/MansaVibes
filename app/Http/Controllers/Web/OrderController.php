<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\InventoryItem;
use App\Models\MeasurementFormTemplate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Services\OrderInventoryDeductionService;
use App\Services\WhatsAppCloudService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->with(['client', 'assignee', 'measurementTemplate'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('modules.orders.index', compact('orders'));
    }

    public function create(Request $request): View
    {
        MeasurementFormTemplate::ensureDefaultsIfNeeded();

        $clients = Client::query()->orderBy('name')->get();
        $employees = Employee::query()->orderBy('name')->get();
        $measurementTemplates = MeasurementFormTemplate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $prefillClientId = null;
        if ($request->filled('client_id')) {
            $cid = (int) $request->query('client_id');
            if ($clients->contains('id', $cid)) {
                $prefillClientId = $cid;
            }
        }

        $inventoryOptions = $this->inventoryOptionsForForms();

        return view('modules.orders.create', compact('clients', 'employees', 'measurementTemplates', 'prefillClientId', 'inventoryOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'measurement_form_template_id' => [
                'nullable',
                'exists:measurement_form_templates,id',
            ],
            'model_notes' => ['nullable', 'string'],
            'advance_payment_cents' => ['nullable', 'integer', 'min:0'],
            'payment_method' => [
                'nullable',
                Rule::requiredIf((int) $request->input('advance_payment_cents', 0) > 0),
                Rule::in(array_keys(Order::paymentMethodLabels())),
            ],
            'delivery_mode' => ['required', Rule::in(['pickup', 'delivery'])],
            'discount_scope' => ['required', 'in:none,all,lines,order'],
            'discount_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.measurement_form_template_id' => [
                'required',
                'exists:measurement_form_templates,id',
            ],
            'items.*.apply_discount' => ['nullable', 'boolean'],
            'items.*.client_supplies_fabric' => ['nullable', 'boolean'],
            'items.*.fabric_price_fcfa' => ['nullable', 'integer', 'min:0'],
            'items.*.inventory_item_id' => [
                'nullable',
                'exists:inventory_items,id',
            ],
            'items.*.inventory_characteristic_key' => ['nullable', 'string', 'max:64'],
            'items.*.inventory_consumed_meters' => ['nullable', 'numeric', 'min:0'],
            'assigned_to' => ['nullable', 'exists:employees,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->validateOrderFabricConsumptionFields($request);

        $reference = 'CMD-'.strtoupper(Str::random(8));

        $templateId = isset($data['measurement_form_template_id']) && $data['measurement_form_template_id'] !== ''
            ? (int) $data['measurement_form_template_id']
            : null;

        $discountScope = in_array($data['discount_scope'], ['order', 'all'], true) ? 'all' : $data['discount_scope'];

        $order = Order::query()->create([
            'client_id' => $data['client_id'],
            'reference' => $reference,
            'measurement_form_template_id' => $templateId,
            'model_name' => null,
            'model_notes' => $data['model_notes'] ?? null,
            'advance_payment_cents' => (int) ($data['advance_payment_cents'] ?? 0),
            'payment_method' => $this->normalizePaymentMethod($data['payment_method'] ?? null),
            'delivery_mode' => $data['delivery_mode'],
            'discount_scope' => $discountScope,
            'discount_percent' => (int) $data['discount_percent'],
            'order_discount_cents' => 0,
            'status' => 'in_progress',
            'due_date' => null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'notes' => $data['notes'] ?? null,
            'total_cents' => 0,
        ]);

        $this->syncOrderItems(
            $order,
            $data['items'],
            $discountScope,
            (int) $data['discount_percent']
        );

        $this->assertAdvanceNotExceedsTotal($order);

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'status' => $order->status,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('orders.index')->with('status', 'Commande créée.');
    }

    public function show(Order $order): View
    {
        $order->load([
            'items.measurementTemplate',
            'items.inventoryItem',
            'images',
            'client',
            'assignee',
            'measurementTemplate',
            'statusHistories.user',
        ]);

        $whatsappCloudReady = app(WhatsAppCloudService::class)->isConfigured();

        return view('modules.orders.show', compact('order', 'whatsappCloudReady'));
    }

    public function edit(Order $order): View
    {
        MeasurementFormTemplate::ensureDefaultsIfNeeded();

        $order->load(['images', 'items']);
        $clients = Client::query()->orderBy('name')->get();
        $employees = Employee::query()->orderBy('name')->get();
        $measurementTemplates = MeasurementFormTemplate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $inventoryOptions = $this->inventoryOptionsForForms();

        return view('modules.orders.edit', compact('order', 'clients', 'employees', 'measurementTemplates', 'inventoryOptions'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['sometimes', 'exists:clients,id'],
            'measurement_form_template_id' => [
                'nullable',
                'exists:measurement_form_templates,id',
            ],
            'model_notes' => ['nullable', 'string'],
            'advance_payment_cents' => ['nullable', 'integer', 'min:0'],
            'payment_method' => [
                'nullable',
                Rule::requiredIf((int) $request->input('advance_payment_cents', 0) > 0),
                Rule::in(array_keys(Order::paymentMethodLabels())),
            ],
            'delivery_mode' => ['required', Rule::in(['pickup', 'delivery'])],
            'discount_scope' => ['required', 'in:none,all,lines,order'],
            'discount_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.measurement_form_template_id' => [
                'required',
                'exists:measurement_form_templates,id',
            ],
            'items.*.apply_discount' => ['nullable', 'boolean'],
            'items.*.client_supplies_fabric' => ['nullable', 'boolean'],
            'items.*.fabric_price_fcfa' => ['nullable', 'integer', 'min:0'],
            'items.*.inventory_item_id' => [
                'nullable',
                'exists:inventory_items,id',
            ],
            'items.*.inventory_characteristic_key' => ['nullable', 'string', 'max:64'],
            'items.*.inventory_consumed_meters' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'max:50'],
            'assigned_to' => ['nullable', 'exists:employees,id'],
            'notes' => ['nullable', 'string'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer', Rule::exists('order_images', 'id')->where('order_id', $order->id)],
        ]);

        $this->validateOrderFabricConsumptionFields($request);

        if (! empty($data['remove_image_ids'])) {
            $ids = $data['remove_image_ids'];
            $toDelete = $order->images()->whereIn('id', $ids)->get();
            foreach ($toDelete as $img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            }
        }

        $templateId = $order->measurement_form_template_id;
        if (array_key_exists('measurement_form_template_id', $data)) {
            $raw = $data['measurement_form_template_id'];
            $templateId = ($raw !== '' && $raw !== null) ? (int) $raw : null;
        }

        $modelName = $templateId
            ? MeasurementFormTemplate::query()->find($templateId)?->name
            : null;

        $discountScope = in_array($data['discount_scope'], ['order', 'all'], true) ? 'all' : $data['discount_scope'];

        $payload = collect($data)->except([
            'remove_image_ids',
            'measurement_form_template_id',
            'items',
            'discount_scope',
            'discount_percent',
        ])->all();
        $payload['measurement_form_template_id'] = $templateId;
        $payload['model_name'] = $modelName;
        $payload['discount_scope'] = $discountScope;
        $payload['discount_percent'] = (int) $data['discount_percent'];
        $payload['payment_method'] = $this->normalizePaymentMethod($data['payment_method'] ?? null);
        $payload['delivery_mode'] = $data['delivery_mode'];
        $payload['due_date'] = null;

        $previousStatus = $order->status;

        DB::transaction(function () use ($request, $order, $data, $discountScope, $previousStatus, $payload): void {
            $order->update($payload);

            $this->syncOrderItems(
                $order,
                $data['items'],
                $discountScope,
                (int) $data['discount_percent']
            );

            $this->assertAdvanceNotExceedsTotal($order);

            $order->refresh();

            if ($order->status === 'delivered' && $previousStatus !== 'delivered' && $order->inventory_deducted_at === null) {
                app(OrderInventoryDeductionService::class)->deduct($order->fresh(['items']));
            }

            if (isset($data['status']) && $data['status'] !== $previousStatus) {
                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'status' => $data['status'],
                    'user_id' => $request->user()->id,
                ]);
            }
        });

        return redirect()->route('orders.show', $order)->with('status', 'Commande mise à jour.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->loadMissing('images');
        foreach ($order->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $order->delete();

        return redirect()->route('orders.index')->with('status', 'Commande supprimée.');
    }

    public function deductInventory(Order $order, OrderInventoryDeductionService $service): RedirectResponse
    {
        $skipped = $service->deduct($order);
        $msg = 'Stock déduit pour cette commande.';
        if ($skipped !== []) {
            $msg .= ' '.implode(' ', $skipped);
        }

        return redirect()->route('orders.show', $order)->with('status', $msg);
    }

    /**
     * @return list<array{id: string, name: string, unit: string, numericRows: list<array{key: string, label: string}>}>
     */
    protected function inventoryOptionsForForms(): array
    {
        return InventoryItem::query()
            ->orderBy('name')
            ->get()
            ->map(function (InventoryItem $i) {
                $numericRows = collect($i->characteristicRowsForActualiser())
                    ->filter(fn ($r) => ($r['type'] ?? '') === 'number')
                    ->values()
                    ->map(fn ($r) => ['key' => (string) $r['key'], 'label' => (string) $r['label']])
                    ->all();

                return [
                    'id' => (string) $i->id,
                    'name' => $i->name,
                    'unit' => (string) $i->unit,
                    'numericRows' => $numericRows,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Si un article avec champs nombre est lié (sans tissu client), exiger ligne + quantité prélevée.
     */
    protected function validateOrderFabricConsumptionFields(Request $request): void
    {
        $items = $request->input('items', []);
        if (! is_array($items)) {
            return;
        }

        foreach (array_values($items) as $idx => $row) {
            if (! is_array($row)) {
                continue;
            }

            $rawInv = $row['inventory_item_id'] ?? null;
            if ($rawInv === null || $rawInv === '' || $rawInv === '0') {
                continue;
            }

            if (filter_var($row['client_supplies_fabric'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            $item = InventoryItem::query()
                ->find((int) $rawInv);

            if ($item === null) {
                continue;
            }

            if (! $item->hasNumericCharacteristicRows()) {
                continue;
            }

            $numericRows = collect($item->characteristicRowsForActualiser())
                ->filter(fn ($r) => ($r['type'] ?? '') === 'number');
            if ($numericRows->isEmpty()) {
                throw ValidationException::withMessages([
                    "items.$idx.inventory_item_id" => 'L’article « '.$item->name.' » n’a pas de champ nombre : paramétrez-le dans Stock d’abord.',
                ]);
            }

            $key = trim((string) ($row['inventory_characteristic_key'] ?? ''));
            $metersRaw = $row['inventory_consumed_meters'] ?? null;
            $meters = ($metersRaw !== null && $metersRaw !== '') ? (float) $metersRaw : 0.0;

            if ($key === '') {
                throw ValidationException::withMessages([
                    "items.$idx.inventory_characteristic_key" => 'Choisissez la ligne (type / détail) pour l’article « '.$item->name.' ».',
                ]);
            }

            if ($meters <= 0) {
                throw ValidationException::withMessages([
                    "items.$idx.inventory_consumed_meters" => 'Indiquez la quantité à prélever ('.$item->unit.') pour « '.$item->name.' ».',
                ]);
            }

            $allowed = collect($item->characteristicRowsForActualiser())
                ->filter(fn ($r) => ($r['type'] ?? '') === 'number')
                ->pluck('key')
                ->map(fn ($k) => (string) $k)
                ->all();

            if (! in_array($key, $allowed, true)) {
                throw ValidationException::withMessages([
                    "items.$idx.inventory_characteristic_key" => 'Ligne de stock invalide pour « '.$item->name.' ».',
                ]);
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsRows
     */
    protected function syncOrderItems(Order $order, array $itemsRows, string $discountScope, int $discountPercent): void
    {
        $scope = in_array($discountScope, ['order', 'all'], true) ? 'all' : $discountScope;

        $order->items()->delete();

        foreach ($itemsRows as $row) {
            $tplId = (int) ($row['measurement_form_template_id'] ?? 0);
            if ($tplId <= 0) {
                continue;
            }

            $template = MeasurementFormTemplate::query()
                ->whereKey($tplId)
                ->first();

            if ($template === null) {
                continue;
            }

            $fabric = filter_var($row['client_supplies_fabric'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $templatePriceCents = max(0, (int) $template->reference_price_fcfa * 100);
            $fabricFcfa = max(0, (int) ($row['fabric_price_fcfa'] ?? 0));
            $price = $fabric ? max(0, $fabricFcfa * 100) : $templatePriceCents;
            $qty = max(1, (int) ($row['quantity'] ?? 1));

            $desc = trim((string) ($row['description'] ?? ''));
            if ($desc === '') {
                $desc = $template->name;
            }

            $applyDisc = filter_var($row['apply_discount'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $discountApplies = $scope === 'lines' && $applyDisc;

            $rawInv = $row['inventory_item_id'] ?? null;
            $inventoryItemId = ($rawInv !== null && $rawInv !== '' && $rawInv !== '0')
                ? (int) $rawInv
                : null;

            $charKey = null;
            $consumedMeters = null;
            if ($inventoryItemId !== null) {
                $inv = InventoryItem::query()->find($inventoryItemId);
                if ($inv !== null && $inv->hasNumericCharacteristicRows()) {
                    $k = trim((string) ($row['inventory_characteristic_key'] ?? ''));
                    $charKey = $k !== '' ? $k : null;
                    $mRaw = $row['inventory_consumed_meters'] ?? null;
                    $consumedMeters = ($mRaw !== null && $mRaw !== '')
                        ? round((float) $mRaw, 3)
                        : null;
                }
            }

            OrderItem::query()->create([
                'order_id' => $order->id,
                'inventory_item_id' => $inventoryItemId,
                'inventory_characteristic_key' => $charKey,
                'inventory_consumed_meters' => $consumedMeters,
                'measurement_form_template_id' => $tplId,
                'description' => $desc,
                'quantity' => $qty,
                'unit_price_cents' => $price,
                'discount_cents' => 0,
                'discount_applies' => $discountApplies,
                'client_supplies_fabric' => $fabric,
            ]);
        }

        $order->refresh();
        $order->discount_scope = $scope;
        $order->discount_percent = min(100, max(0, $discountPercent));
        $order->saveQuietly();
        $order->recalculateTotals();

        $order->load(['items.measurementTemplate']);
        $firstTpl = $order->items->first()?->measurement_form_template_id;
        $label = $order->items
            ->map(fn (OrderItem $i) => $i->measurementTemplate?->name ?: $i->description)
            ->filter()
            ->unique()
            ->implode(', ');

        $order->measurement_form_template_id = $firstTpl;
        $order->model_name = $label !== '' ? $label : null;
        $order->saveQuietly();
    }

    protected function normalizePaymentMethod(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        return is_string($raw) ? $raw : null;
    }

    protected function assertAdvanceNotExceedsTotal(Order $order): void
    {
        $order->refresh();
        $total = (int) $order->total_cents;
        $advance = (int) $order->advance_payment_cents;

        if ($advance > $total) {
            throw ValidationException::withMessages([
                'advance_payment_cents' => 'Le montant versé ne peut pas dépasser le total ('.number_format(max(0, $total) / 100, 0, ',', ' ').' FCFA). Pour un paiement intégral, saisissez exactement le total.',
            ]);
        }
    }

}
