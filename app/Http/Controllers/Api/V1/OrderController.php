<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return JsonResource::collection(
            Order::query()
                ->with(['client', 'assignee'])
                ->orderByDesc('created_at')
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResource
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'status' => ['nullable', 'string', 'max:50'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:employees,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.description' => ['required_with:items', 'string'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.unit_price_cents' => ['nullable', 'integer', 'min:0'],
        ]);

        $this->assertClientInTenant($data['client_id']);

        $reference = $this->nextReference();

        $order = Order::query()->create([
            'client_id' => $data['client_id'],
            'reference' => $reference,
            'status' => $data['status'] ?? 'pending',
            'due_date' => $data['due_date'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'notes' => $data['notes'] ?? null,
            'total_cents' => 0,
        ]);

        $total = 0;
        foreach ($data['items'] ?? [] as $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $price = (int) ($item['unit_price_cents'] ?? 0);
            $line = $qty * $price;
            $total += $line;
            OrderItem::query()->create([
                'order_id' => $order->id,
                'description' => $item['description'],
                'quantity' => $qty,
                'unit_price_cents' => $price,
            ]);
        }

        if ($total > 0) {
            $order->update(['total_cents' => $total]);
        }

        $this->recordStatus($order, $order->status);

        return new JsonResource($order->load(['items', 'client', 'assignee']));
    }

    public function show(Order $order): JsonResource
    {
        return new JsonResource($order->load(['items', 'client', 'assignee', 'statusHistories']));
    }

    public function update(Request $request, Order $order): JsonResource
    {
        $data = $request->validate([
            'client_id' => ['sometimes', 'exists:clients,id'],
            'status' => ['sometimes', 'string', 'max:50'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:employees,id'],
            'notes' => ['nullable', 'string'],
        ]);

        if (isset($data['client_id'])) {
            $this->assertClientInTenant((int) $data['client_id']);
        }

        $previous = $order->status;
        $order->update($data);

        if (isset($data['status']) && $data['status'] !== $previous) {
            $this->recordStatus($order, (string) $data['status']);
        }

        return new JsonResource($order->load(['items', 'client', 'assignee']));
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json(['ok' => true]);
    }

    protected function assertClientInTenant(int $clientId): void
    {
        Client::query()->whereKey($clientId)->firstOrFail();
    }

    protected function nextReference(): string
    {
        return 'CMD-'.strtoupper(Str::random(8));
    }

    protected function recordStatus(Order $order, string $status): void
    {
        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'status' => $status,
            'user_id' => Auth::id(),
        ]);
    }
}
