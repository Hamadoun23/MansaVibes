<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-mansa-black leading-tight">Modifier la catégorie</h2>
    </x-slot>
    <div class="py-8 max-w-lg mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('finance.categories.update', $category) }}" class="bg-white border border-gold-100 rounded-lg p-6 space-y-4 shadow-sm">
            @csrf @method('PUT')
            <div>
                <x-input-label for="name" value="Nom" />
                <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $category->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="type" value="Type" />
                <select id="type" name="type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="expense" @selected(old('type', $category->type) === 'expense')>Dépense</option>
                    <option value="income" @selected(old('type', $category->type) === 'income')>Entrée</option>
                </select>
            </div>
            <div>
                <x-input-label for="sort_order" value="Ordre" />
                <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="block mt-1 w-full" :value="old('sort_order', $category->sort_order)" />
            </div>
            <div class="flex justify-between">
                <a href="{{ route('finance.categories.index') }}" class="text-sm text-gray-600">Retour</a>
                <x-primary-button>Mettre à jour</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
