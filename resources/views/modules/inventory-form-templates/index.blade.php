<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Modèles de fiche stock</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-4 py-2 border border-gold-300 rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-50">
                    Stock &amp; matières
                </a>
                <a href="{{ route('inventory-form-templates.create') }}" class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-400">
                    Nouveau modèle
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
        @endif

        <p class="text-sm text-gray-600">
            Définissez les <strong>champs paramétrables</strong> pour vos articles (tissu, mercerie, etc.), comme pour les mensurations.
            Chaque article choisit un modèle et vous saisissez les valeurs (couleurs, qualité, dimensions…). Les modèles <strong>inactifs</strong> ne sont plus proposés sur les nouveaux articles.
        </p>

        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Modèle</th>
                        <th class="px-4 py-2 text-left">Type cible</th>
                        <th class="px-4 py-2 text-left">Statut</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($templates as $t)
                        <tr>
                            <td class="px-4 py-2 font-medium text-mansa-black">{{ $t->name }}</td>
                            <td class="px-4 py-2 text-gray-600">
                                @if ($t->applies_to_stock_type === 'fabric')
                                    Tissu / matière
                                @elseif ($t->applies_to_stock_type === 'accessory')
                                    Accessoire / mercerie
                                @elseif ($t->applies_to_stock_type === 'other')
                                    Autre
                                @else
                                    Tous types
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @if ($t->is_active)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Actif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-medium text-gray-700">Inactif</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('inventory-form-templates.create', ['from' => $t->id]) }}" class="text-gray-700 hover:text-mansa-black font-medium">Dupliquer</a>
                                <a href="{{ route('inventory-form-templates.edit', $t) }}" class="text-gold-700 font-medium">Modifier</a>
                                <form action="{{ route('inventory-form-templates.destroy', $t) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce modèle ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Aucun modèle.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-2">{{ $templates->links() }}</div>
    </div>
</x-app-layout>
