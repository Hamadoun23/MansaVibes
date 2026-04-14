<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Catégories financières</h2>
            <div class="flex gap-2 text-sm">
                <a href="{{ route('finance.index') }}" class="text-gray-600 hover:text-gray-900">← Finance</a>
                <a href="{{ route('finance.categories.create') }}" class="text-gold-700 font-medium">+ Nouvelle catégorie</a>
            </div>
        </div>
    </x-slot>
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
        @endif
        <p class="text-sm text-gray-600">Types personnalisés pour classer les <strong>dépenses</strong> et les <strong>entrées</strong> de caisse.</p>
        <div class="bg-white border border-gold-100 rounded-lg overflow-hidden shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr class="text-left text-xs text-gray-500 uppercase"><th class="px-4 py-2">Nom</th><th class="px-4 py-2">Type</th><th class="px-4 py-2">Ordre</th><th class="px-4 py-2"></th></tr></thead>
                <tbody class="divide-y">
                    @foreach ($categories as $cat)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $cat->name }}</td>
                            <td class="px-4 py-2">{{ $cat->type === 'income' ? 'Entrée' : 'Dépense' }}</td>
                            <td class="px-4 py-2">{{ $cat->sort_order }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('finance.categories.edit', $cat) }}" class="text-gold-700">Modifier</a>
                                <form action="{{ route('finance.categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $categories->links() }}</div>
    </div>
</x-app-layout>
