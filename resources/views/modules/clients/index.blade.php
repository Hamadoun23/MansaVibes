<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">{{ __('clients.list_page_title') }}</h2>
            <a href="{{ route('clients.create') }}" class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-400">
                {{ __('clients.new_client_button') }}
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('clients.name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('clients.phone') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('clients.email') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($clients as $client)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $client->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $client->phone ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $client->email ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-right space-x-2">
                                    <a href="{{ route('clients.show', $client) }}" class="text-gold-700 hover:text-gold-900 font-medium">{{ __('clients.profile_link') }}</a>
                                    <a href="{{ route('clients.edit', $client) }}" class="text-gold-700 hover:text-gold-900 font-medium">{{ __('clients.edit_short') }}</a>
                                    @if (auth()->user()->role !== 'tailleur')
                                        <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce client ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">{{ __('clients.delete') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500 text-sm">{{ __('clients.empty_list') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-2">{{ $clients->links() }}</div>
        </div>
    </div>
</x-app-layout>
