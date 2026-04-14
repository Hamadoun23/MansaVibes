<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-mansa-black leading-tight">Nouvelle immobilisation</h2></x-slot>
    <div class="py-8 max-w-lg mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('finance.fixed-assets.store') }}" class="bg-white border border-gold-100 rounded-lg p-6 space-y-4 shadow-sm">
            @csrf
            <div>
                <x-input-label for="name" value="Libellé (machine, véhicule…)" />
                <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" required />
            </div>
            <div>
                <x-input-label for="acquisition_date" value="Date d’acquisition" />
                <x-text-input id="acquisition_date" name="acquisition_date" type="date" class="block mt-1 w-full" :value="old('acquisition_date', now()->format('Y-m-d'))" required />
            </div>
            <div>
                <x-input-label for="amount_fcfa" value="Montant (FCFA)" />
                <x-text-input id="amount_fcfa" name="amount_fcfa" type="number" min="1" step="1" class="block mt-1 w-full" :value="old('amount_fcfa')" required />
            </div>
            <div>
                <x-input-label for="useful_life_months" value="Durée d’amortissement (mois, optionnel)" />
                <x-text-input id="useful_life_months" name="useful_life_months" type="number" min="1" class="block mt-1 w-full" :value="old('useful_life_months')" />
            </div>
            <div>
                <x-input-label for="notes" value="Notes" />
                <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md text-sm">{{ old('notes') }}</textarea>
            </div>
            <div class="flex justify-between">
                <a href="{{ route('finance.fixed-assets.index') }}" class="text-sm text-gray-600">Annuler</a>
                <x-primary-button>Enregistrer</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
