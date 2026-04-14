<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Paramétrer l’article</h2>
            <a href="{{ route('inventory.index') }}" class="text-sm text-gold-700 hover:text-gold-900 font-medium">Retour à la liste</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
            <p class="text-xs text-gray-500">Nom, unité, seuil et intitulés des champs. Les quantités se saisissent sur <strong>Actualiser</strong>.</p>
        </div>
        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6">
            <form method="POST" action="{{ route('inventory.parameterize.update', $item) }}" class="space-y-8">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-mansa-black">Article &amp; alertes</h3>
                    @include('modules.inventory._article_settings_fields', ['item' => $item])
                    <div>
                        <x-input-label for="supplier_id" value="Fournisseur (optionnel)" />
                        <select id="supplier_id" name="supplier_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">— Aucun —</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}" @selected((string) old('supplier_id', $item->supplier_id) === (string) $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('supplier_id')" class="mt-1" />
                    </div>
                </div>
                <div class="border-t border-gold-100 pt-6">
                    <h3 class="text-sm font-semibold text-mansa-black mb-4">Intitulés des caractéristiques</h3>
                    @include('modules.inventory._characteristics_schema_form', ['item' => $item, 'schemaLinesForJs' => $schemaLinesForJs])
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <a href="{{ route('inventory.index') }}" class="text-sm text-gray-600 hover:text-gray-900 py-2">Annuler</a>
                    <x-primary-button>Enregistrer</x-primary-button>
                </div>
            </form>
        </div>
        <div class="bg-white border border-red-100 sm:rounded-lg p-4">
            <p class="text-sm text-gray-700 mb-2">Zone sensible</p>
            <form action="{{ route('inventory.destroy', $item) }}" method="POST" onsubmit="return confirm('Supprimer cet article ? Les mouvements de stock liés seront aussi supprimés.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Supprimer l’article</button>
            </form>
        </div>
    </div>
</x-app-layout>
