<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-mansa-black leading-tight">E-commerce (vitrine)</h2>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left">Produit</th><th class="px-4 py-2 text-left">Slug</th><th class="px-4 py-2 text-right">Prix</th><th class="px-4 py-2 text-center">Actif</th></tr></thead>
                <tbody class="divide-y">
                    @forelse ($products as $p)
                        <tr>
                            <td class="px-4 py-2">{{ $p->name }}</td>
                            <td class="px-4 py-2 font-mono text-xs">{{ $p->slug }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($p->price_cents / 100, 2, ',', ' ') }} €</td>
                            <td class="px-4 py-2 text-center">{{ $p->is_active ? 'Oui' : 'Non' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Aucun produit catalogue.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    </div>
</x-app-layout>
