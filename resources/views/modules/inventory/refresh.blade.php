<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Actualiser le stock</h2>
            <a href="{{ route('inventory.index') }}" class="text-sm text-gold-700 hover:text-gold-900 font-medium">Retour à la liste</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
            <p><span class="font-medium text-mansa-black">Article :</span> {{ $item->name }}</p>
            <p class="mt-1"><span class="font-medium">Unité :</span> {{ $item->unit }} · <span class="font-medium">Seuil :</span> {{ \App\Models\InventoryItem::formatStockForList($item->reorder_level) }}</p>
            <p class="mt-2 text-xs">
                <a href="{{ route('inventory.parameterize', $item) }}" class="text-gold-700 font-medium hover:underline">Modifier nom, seuil ou intitulés des champs</a>
            </p>
        </div>
        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6">
            @if ($schemaRows !== [])
                <form method="POST" action="{{ route('inventory.refresh.update', $item) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')
                    @include('modules.inventory._characteristics_values_form', ['item' => $item, 'schemaRows' => $schemaRows])
                    <div class="flex justify-end gap-2 pt-2">
                        <a href="{{ route('inventory.index') }}" class="text-sm text-gray-600 hover:text-gray-900 py-2">Annuler</a>
                        <x-primary-button>Enregistrer le stock</x-primary-button>
                    </div>
                </form>
            @else
                @include('modules.inventory._characteristics_values_form', ['item' => $item, 'schemaRows' => $schemaRows])
                <div class="mt-6 flex justify-end">
                    <a href="{{ route('inventory.parameterize', $item) }}" class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-400">
                        Paramétrer les caractéristiques
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
