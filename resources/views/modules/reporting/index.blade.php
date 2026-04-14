<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-mansa-black leading-tight">Reporting</h2>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6">
            <p class="text-sm text-gray-700">Commandes enregistrées pour cet atelier : <strong>{{ $ordersCount }}</strong></p>
        </div>
        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-4">
            <h3 class="font-medium mb-2">Snapshots agrégés</h3>
            <ul class="text-sm text-gray-600 space-y-1">
                @forelse ($snapshots as $s)
                    <li>{{ $s->period_start->format('d/m/Y') }} → {{ $s->period_end->format('d/m/Y') }} — {{ json_encode($s->metrics) }}</li>
                @empty
                    <li class="text-gray-500">Aucun snapshot (modules analytiques à brancher).</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
