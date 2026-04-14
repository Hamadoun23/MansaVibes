<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Nouveau fournisseur</h2>
            <a href="{{ route('suppliers.index') }}" class="text-sm text-gold-700 hover:text-gold-900 font-medium">Retour</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6">
            <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-6">
                @csrf
                @include('modules.suppliers._form', ['supplier' => null])
                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('suppliers.index') }}" class="text-sm text-gray-600 hover:text-gray-900 py-2">Annuler</a>
                    <x-primary-button>Enregistrer</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
