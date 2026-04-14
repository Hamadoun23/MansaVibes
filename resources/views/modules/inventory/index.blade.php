<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Stock &amp; matières</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('suppliers.index') }}" class="inline-flex items-center px-4 py-2 border border-gold-400 rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-50">
                    Fournisseurs
                </a>
                <a href="{{ route('inventory.reception.create') }}" class="inline-flex items-center px-4 py-2 border border-gold-400 rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-50">
                    Réception
                </a>
                <a href="{{ route('inventory.create') }}" class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-400">
                    Nouvel article
                </a>
            </div>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
        @endif

        @if ($items->isEmpty())
            <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg px-4 py-8 text-center text-gray-500">
                <p>Aucun article en stock.</p>
                <a href="{{ route('inventory.create') }}" class="mt-2 inline-block text-gold-700 font-medium hover:underline">Créer un premier article</a>
            </div>
        @else
            <ul class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3" role="list">
                @foreach ($items as $item)
                    @php
                        $low = (float) $item->quantity_on_hand <= (float) $item->reorder_level;
                        $charShort = \Illuminate\Support\Str::limit($item->characteristicsSummary(), 120);
                    @endphp
                    <li class="rounded-lg border border-gold-100 bg-white p-4 shadow-sm {{ $low ? 'bg-amber-50/90 border-amber-200' : '' }}">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="space-y-1 min-w-0 flex-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $item->stockTypeBadgeClass() }}">
                                    {{ $item->stockTypeLabel() }}
                                </span>
                                <p class="font-medium text-mansa-black leading-snug">{{ $item->name }}</p>
                                @if ($low)
                                    <p class="text-xs font-semibold text-amber-800">Stock faible</p>
                                @endif
                            </div>
                            <div class="shrink-0 flex flex-col items-end gap-1">
                                <a href="{{ route('inventory.parameterize', $item) }}" class="text-sm font-medium text-gold-800 hover:text-gold-950">Paramétrer</a>
                                <a href="{{ route('inventory.refresh', $item) }}" class="text-sm font-medium text-mansa-black hover:text-gold-900">Actualiser</a>
                            </div>
                        </div>
                        @if ($item->supplier)
                            <p class="mt-2 text-xs text-gray-600">Fournisseur : <span class="font-medium text-gray-800">{{ $item->supplier->name }}</span></p>
                        @endif
                        @if ($item->formTemplate)
                            <p class="mt-2 text-xs text-gray-600">Fiche : {{ $item->formTemplate->name }}</p>
                        @endif
                        @if ($charShort !== '' && $charShort !== '—')
                            <p class="mt-2 text-xs text-gray-600"><span class="font-medium text-gray-700">Caract. :</span> {{ $charShort }}</p>
                        @endif
                        <dl class="mt-3 grid grid-cols-2 gap-2 text-sm border-t border-gray-100 pt-3">
                            <div>
                                <dt class="text-xs text-gray-500">Qté</dt>
                                <dd class="font-medium">{{ \App\Models\InventoryItem::formatStockForList($item->quantity_on_hand) }} {{ $item->unit }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500">Seuil</dt>
                                <dd class="font-medium">{{ \App\Models\InventoryItem::formatStockForList($item->reorder_level) }}</dd>
                            </div>
                        </dl>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="mt-4 px-1 sm:px-0">{{ $items->links() }}</div>
    </div>
</x-app-layout>
