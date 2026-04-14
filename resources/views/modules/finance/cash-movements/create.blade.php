<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-mansa-black leading-tight">Saisir un mouvement de caisse</h2></x-slot>
    <div class="py-8 max-w-lg mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('finance.cash-movements.store') }}" class="bg-white border border-gold-100 rounded-lg p-6 space-y-4 shadow-sm">
            @csrf
            <div>
                <x-input-label for="direction" value="Sens" />
                <select id="direction" name="direction" class="block mt-1 w-full border-gray-300 rounded-md text-sm">
                    <option value="in">Entrée d’argent</option>
                    <option value="out">Sortie (dépense)</option>
                </select>
            </div>
            <div>
                <x-input-label for="amount_fcfa" value="Montant (FCFA)" />
                <x-text-input id="amount_fcfa" name="amount_fcfa" type="number" step="1" min="1" class="block mt-1 w-full" :value="old('amount_fcfa')" required />
                <x-input-error :messages="$errors->get('amount_fcfa')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="label" value="Libellé" />
                <x-text-input id="label" name="label" class="block mt-1 w-full" :value="old('label')" required />
            </div>
            <div>
                <x-input-label for="movement_date" value="Date" />
                <x-text-input id="movement_date" name="movement_date" type="date" class="block mt-1 w-full" :value="old('movement_date', now()->format('Y-m-d'))" required />
            </div>
            <div>
                <x-input-label for="finance_category_id" value="Catégorie (optionnel)" />
                <select id="finance_category_id" name="finance_category_id" class="block mt-1 w-full border-gray-300 rounded-md text-sm">
                    <option value="">—</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('finance_category_id') == $c->id)>{{ $c->name }} ({{ $c->type === 'income' ? 'entrée' : 'dépense' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="notes" value="Notes" />
                <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md text-sm">{{ old('notes') }}</textarea>
            </div>
            <div class="flex justify-between">
                <a href="{{ route('finance.cash-movements.index') }}" class="text-sm text-gray-600">Annuler</a>
                <x-primary-button>Enregistrer</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
