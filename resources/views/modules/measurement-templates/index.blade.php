<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Modèles de mensuration</h2>
            <a href="{{ route('measurement-templates.create') }}" class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-400">
                Nouveau modèle
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
        @endif

        <p class="text-sm text-gray-600">
            Chaque modèle correspond à un type de vêtement : les champs du formulaire sur la fiche client s’adaptent au modèle choisi (robe, costume, enfant…).
            Pour <strong>activer ou désactiver</strong> un modèle, ouvrez <strong>Modifier</strong> (bloc <strong>Statut du modèle</strong>).
            <strong>Dupliquer</strong> ouvre la création avec les données déjà chargées ; vous pouvez aussi choisir un modèle sur la page de création.
        </p>

        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Nom du modèle</th>
                        <th class="px-4 py-2 text-left">Prix ref.</th>
                        <th class="px-4 py-2 text-left">Statut</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($templates as $t)
                        <tr>
                            <td class="px-4 py-2 font-medium text-mansa-black">{{ $t->name }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ number_format((int) ($t->reference_price_fcfa ?? 0), 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-2">
                                @if ($t->is_active)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Actif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-medium text-gray-700">Inactif</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('measurement-templates.create', ['from' => $t->id]) }}" class="text-gray-700 hover:text-mansa-black font-medium">Dupliquer</a>
                                <a href="{{ route('measurement-templates.edit', $t) }}" class="text-gold-700 font-medium">Modifier</a>
                                <form action="{{ route('measurement-templates.destroy', $t) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce modèle ? Les fiches déjà enregistrées restent valides.');">
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
