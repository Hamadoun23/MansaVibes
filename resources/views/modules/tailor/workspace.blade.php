<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-mansa-black leading-tight">
            {{ __('tailor.workspace_title', ['name' => $employee->name, 'shop' => \App\Models\AppSettings::businessName()]) }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-stone-600">{{ __('tailor.subtitle') }}</p>
                <a
                    href="{{ route('tailor.clients.measurements.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-400 shadow-sm"
                >
                    {{ __('tailor.new_client_measurement_btn') }}
                </a>
            </div>

            <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('tailor.ref') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('tailor.client') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('tailor.model') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('tailor.status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('tailor.delivery') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('tailor.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 font-mono">{{ $order->reference }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $order->client?->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $order->displayModelLabel() }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $order->statusLabel() }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $order->due_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-right space-x-2">
                                    <a href="{{ route('orders.show', $order) }}" class="text-gold-700 hover:text-gold-900 font-medium">{{ __('tailor.detail') }}</a>
                                    @if (! in_array($order->status, ['validated', 'delivered'], true))
                                        <form method="POST" action="{{ route('tailor.orders.validate', $order) }}" class="inline" onsubmit="return confirm(@json(__('tailor.confirm_validate')));">
                                            @csrf
                                            <button type="submit" class="text-green-700 hover:text-green-900 font-medium">{{ __('tailor.mark_validated') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500 text-sm">{{ __('tailor.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-2">{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>
