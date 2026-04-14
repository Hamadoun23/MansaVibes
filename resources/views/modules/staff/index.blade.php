<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-mansa-black leading-tight">Équipe &amp; employés</h2>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
        @endif
        <p class="text-sm text-gray-600">Salaire mensuel utilisé pour la <strong>masse salariale</strong> et les indicateurs finance (FCFA).</p>
        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg overflow-hidden overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Nom</th>
                        <th class="px-4 py-2 text-left">Rôle</th>
                        <th class="px-4 py-2 text-left">Téléphone</th>
                        <th class="px-4 py-2 text-left">Salaire mensuel (FCFA)</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($employees as $e)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $e->name }}</td>
                            <td class="px-4 py-2">{{ $e->role_title ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $e->phone ?? '—' }}</td>
                            <td class="px-4 py-2">
                                <form method="POST" action="{{ route('staff.employees.salary', $e) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="monthly_salary_fcfa" min="0" step="1" value="{{ old('monthly_salary_fcfa.'.$e->id, $e->monthly_salary_cents / 100) }}" class="w-32 rounded-md border-gray-300 text-sm" />
                                    <button type="submit" class="text-xs font-semibold text-gold-800 hover:text-gold-950">OK</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Aucun employé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $employees->links() }}</div>
    </div>
</x-app-layout>
