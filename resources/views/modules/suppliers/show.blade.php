<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">{{ $supplier->name }}</h2>
            <div class="flex flex-wrap gap-3 text-sm">
                <a href="{{ route('inventory.reception.create', ['supplier' => $supplier->id]) }}" class="font-medium text-mansa-black hover:text-gold-900">Réception de stock</a>
                <a href="{{ route('suppliers.edit', $supplier) }}" class="text-gold-700 hover:text-gold-900 font-medium">Modifier</a>
                <a href="{{ route('suppliers.index') }}" class="text-gray-600 hover:text-gray-900">Liste</a>
            </div>
        </div>
    </x-slot>
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
        @endif

        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6 space-y-3 text-sm">
            <dl class="grid gap-2 sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-gray-500">Téléphone</dt>
                    <dd class="font-medium text-mansa-black">{{ $supplier->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Email</dt>
                    <dd class="font-medium text-mansa-black">{{ $supplier->email ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-gray-500">Adresse</dt>
                    <dd class="text-gray-800 whitespace-pre-line">{{ $supplier->address ?? '—' }}</dd>
                </div>
                @if ($supplier->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-gray-500">Notes</dt>
                        <dd class="text-gray-800 whitespace-pre-line">{{ $supplier->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-mansa-black mb-3">Dernières entrées de stock</h3>
            <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Article</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qté</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Réf.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($movements as $m)
                            <tr>
                                <td class="px-4 py-2 text-gray-600 whitespace-nowrap">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2 text-gray-900">{{ $m->item?->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-right tabular-nums font-medium text-emerald-800">+{{ \App\Models\InventoryItem::formatStockForList($m->quantity_delta) }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $m->reference ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">Aucune réception enregistrée pour ce fournisseur.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
