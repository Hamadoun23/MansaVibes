<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Commandes</h2>
            <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-400">
                Nouvelle commande
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Réf.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modèle</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 font-mono">{{ $order->reference }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $order->client?->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $order->displayModelLabel() }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $order->statusLabel() }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ number_format($order->total_cents / 100, 0, ',', ' ') }} FCFA</td>
                                <td class="px-4 py-3 text-sm text-right space-x-2">
                                    <a href="{{ route('orders.show', $order) }}" class="text-gold-700 hover:text-gold-900 font-medium">Voir</a>
                                    <a href="{{ route('orders.edit', $order) }}" class="text-gold-700 hover:text-gold-900 font-medium">Modifier</a>
                                    <form action="{{ route('orders.destroy', $order) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette commande ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500 text-sm">Aucune commande.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-2">{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>
