<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-mansa-black leading-tight">Communications</h2>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-4">
            <h3 class="font-medium mb-2">Journal des notifications</h3>
            <ul class="text-sm text-gray-600 space-y-2">
                @forelse ($logs as $log)
                    <li><span class="font-mono text-xs">{{ $log->channel }}</span> → {{ $log->recipient }} : {{ $log->status }}</li>
                @empty
                    <li class="text-gray-500">Aucun envoi enregistré (WhatsApp/SMS à intégrer).</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
