<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return JsonResource::collection(
            Client::query()->orderBy('name')->paginate(20)
        );
    }

    public function store(Request $request): JsonResource
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'balance_cents' => ['nullable', 'integer'],
        ]);

        $client = Client::query()->create($data);

        return new JsonResource($client);
    }

    public function show(Client $client): JsonResource
    {
        return new JsonResource($client->load('measurements'));
    }

    public function update(Request $request, Client $client): JsonResource
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'balance_cents' => ['nullable', 'integer'],
        ]);

        $client->update($data);

        return new JsonResource($client);
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json(['ok' => true]);
    }
}
