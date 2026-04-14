<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Fournisseurs</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('inventory.reception.create') }}" class="inline-flex items-center px-4 py-2 border border-gold-400 rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-50">
                    Réception de stock
                </a>
                <a href="{{ route('suppliers.create') }}" class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-400">
                    Nouveau fournisseur
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
        @endif

        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $supplier->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $supplier->phone ?? '—' }}
                                @if ($supplier->email)
                                    <span class="block text-xs">{{ $supplier->email }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right space-x-2">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="text-gold-700 hover:text-gold-900 font-medium">Fiche</a>
                                <a href="{{ route('inventory.reception.create', ['supplier' => $supplier->id]) }}" class="text-mansa-black hover:text-gold-900 font-medium">Réception</a>
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="text-gold-700 hover:text-gold-900 font-medium">Modifier</a>
                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce fournisseur ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-gray-500 text-sm">Aucun fournisseur. Créez-en un ou enregistrez une réception de stock.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $suppliers->links() }}</div>
    </div>
</x-app-layout>
