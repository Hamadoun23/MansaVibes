<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Immobilisations</h2>
            <div class="flex gap-2 text-sm">
                <a href="{{ route('finance.index') }}" class="text-gray-600 hover:text-gray-900">← Finance</a>
                <a href="{{ route('finance.fixed-assets.create') }}" class="text-gold-700 font-medium">+ Ajouter</a>
            </div>
        </div>
    </x-slot>
    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
        @endif
        <div class="bg-white border border-gold-100 rounded-lg overflow-hidden shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr class="text-left text-xs text-gray-500 uppercase"><th class="px-4 py-2">Libellé</th><th class="px-4 py-2">Date</th><th class="px-4 py-2">Durée (mois)</th><th class="px-4 py-2 text-right">Valeur FCFA</th><th class="px-4 py-2"></th></tr></thead>
                <tbody class="divide-y">
                    @foreach ($assets as $a)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $a->name }}</td>
                            <td class="px-4 py-2">{{ $a->acquisition_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">{{ $a->useful_life_months ?? '—' }}</td>
                            <td class="px-4 py-2 text-right font-mono">{{ number_format($a->amount_cents / 100, 0, ',', ' ') }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('finance.fixed-assets.edit', $a) }}" class="text-gold-700">Modifier</a>
                                <form action="{{ route('finance.fixed-assets.destroy', $a) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?');">@csrf @method('DELETE')<button type="submit" class="text-red-600">Suppr.</button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $assets->links() }}</div>
    </div>
</x-app-layout>
