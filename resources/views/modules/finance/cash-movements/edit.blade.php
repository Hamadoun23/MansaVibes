<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-mansa-black leading-tight">Modifier le mouvement</h2></x-slot>
    <div class="py-8 max-w-lg mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('finance.cash-movements.update', $cashMovement) }}" class="bg-white border border-gold-100 rounded-lg p-6 space-y-4 shadow-sm">
            @csrf @method('PUT')
            <div>
                <x-input-label for="direction" value="Sens" />
                <select id="direction" name="direction" class="block mt-1 w-full border-gray-300 rounded-md text-sm">
                    <option value="in" @selected(old('direction', $cashMovement->direction) === 'in')>Entrée</option>
                    <option value="out" @selected(old('direction', $cashMovement->direction) === 'out')>Sortie</option>
                </select>
            </div>
            <div>
                <x-input-label for="amount_fcfa" value="Montant (FCFA)" />
                <x-text-input id="amount_fcfa" name="amount_fcfa" type="number" step="1" min="1" class="block mt-1 w-full" :value="old('amount_fcfa', $cashMovement->amount_cents / 100)" required />
            </div>
            <div>
                <x-input-label for="label" value="Libellé" />
                <x-text-input id="label" name="label" class="block mt-1 w-full" :value="old('label', $cashMovement->label)" required />
            </div>
            <div>
                <x-input-label for="movement_date" value="Date" />
                <x-text-input id="movement_date" name="movement_date" type="date" class="block mt-1 w-full" :value="old('movement_date', $cashMovement->movement_date->format('Y-m-d'))" required />
            </div>
            <div>
                <x-input-label for="finance_category_id" value="Catégorie" />
                <select id="finance_category_id" name="finance_category_id" class="block mt-1 w-full border-gray-300 rounded-md text-sm">
                    <option value="">—</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('finance_category_id', $cashMovement->finance_category_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="notes" value="Notes" />
                <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md text-sm">{{ old('notes', $cashMovement->notes) }}</textarea>
            </div>
            <div class="flex justify-between">
                <a href="{{ route('finance.cash-movements.index') }}" class="text-sm text-gray-600">Retour</a>
                <x-primary-button>Mettre à jour</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
