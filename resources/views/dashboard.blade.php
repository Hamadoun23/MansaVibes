<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-mansa-black leading-tight">
            Tableau de bord — {{ auth()->user()->tenant->name }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <p class="text-sm text-stone-600">Accès rapide aux modules Mansa Vibes (PWA installable depuis le navigateur).</p>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('clients.index') }}" class="block rounded-xl border border-gold-200 bg-white p-5 shadow-sm hover:border-gold-400 hover:shadow-md transition">
                    <h3 class="font-semibold text-mansa-black">CRM — Clients</h3>
                    <p class="text-sm text-stone-600 mt-1">Fiches, mensurations, historique.</p>
                </a>
                <a href="{{ route('orders.index') }}" class="block rounded-xl border border-gold-200 bg-white p-5 shadow-sm hover:border-gold-400 hover:shadow-md transition">
                    <h3 class="font-semibold text-mansa-black">Commandes</h3>
                    <p class="text-sm text-stone-600 mt-1">Statuts, assignation, lignes.</p>
                </a>
                <a href="{{ route('finance.index') }}" class="block rounded-xl border border-gold-200 bg-white p-5 shadow-sm hover:border-gold-400 hover:shadow-md transition">
                    <h3 class="font-semibold text-mansa-black">Finance</h3>
                    <p class="text-sm text-stone-600 mt-1">Mouvements &amp; caisse.</p>
                </a>
                <a href="{{ route('staff.index') }}" class="block rounded-xl border border-gold-200 bg-white p-5 shadow-sm hover:border-gold-400 hover:shadow-md transition">
                    <h3 class="font-semibold text-mansa-black">Équipe</h3>
                    <p class="text-sm text-stone-600 mt-1">Employés &amp; tâches.</p>
                </a>
                <a href="{{ route('inventory.index') }}" class="block rounded-xl border border-gold-200 bg-white p-5 shadow-sm hover:border-gold-400 hover:shadow-md transition">
                    <h3 class="font-semibold text-mansa-black">Stock</h3>
                    <p class="text-sm text-stone-600 mt-1">Tissus &amp; mercerie — <span class="text-gold-800 font-medium">Réception</span></p>
                </a>
                <a href="{{ route('suppliers.index') }}" class="block rounded-xl border border-gold-200 bg-white p-5 shadow-sm hover:border-gold-400 hover:shadow-md transition">
                    <h3 class="font-semibold text-mansa-black">Fournisseurs</h3>
                    <p class="text-sm text-stone-600 mt-1">Annuaire &amp; entrées de stock.</p>
                </a>
                <a href="{{ route('commerce.index') }}" class="block rounded-xl border border-gold-200 bg-white p-5 shadow-sm hover:border-gold-400 hover:shadow-md transition">
                    <h3 class="font-semibold text-mansa-black">E-commerce</h3>
                    <p class="text-sm text-stone-600 mt-1">Catalogue vitrine.</p>
                </a>
                <a href="{{ route('reporting.index') }}" class="block rounded-xl border border-gold-200 bg-white p-5 shadow-sm hover:border-gold-400 hover:shadow-md transition">
                    <h3 class="font-semibold text-mansa-black">Reporting</h3>
                    <p class="text-sm text-stone-600 mt-1">Indicateurs &amp; snapshots.</p>
                </a>
                <a href="{{ route('communications.index') }}" class="block rounded-xl border border-gold-200 bg-white p-5 shadow-sm hover:border-gold-400 hover:shadow-md transition">
                    <h3 class="font-semibold text-mansa-black">Communications</h3>
                    <p class="text-sm text-stone-600 mt-1">Notifications &amp; canaux.</p>
                </a>
            </div>

            @php($sampleInvoice = \App\Models\Invoice::query()->first())
            @if ($sampleInvoice)
                <div class="rounded-lg bg-white border border-gold-200 p-4 text-sm text-mansa-black shadow-sm">
                    Exemple facture PDF :
                    <a class="text-gold-700 font-medium hover:text-gold-900" href="{{ route('invoices.pdf', $sampleInvoice) }}" target="_blank" rel="noopener">Télécharger FAC {{ $sampleInvoice->number }}</a>
                    <span class="text-stone-400"> | </span>
                    <a class="text-gold-700 hover:text-gold-900" href="{{ route('invoices.pdf', $sampleInvoice) }}?preview=1" target="_blank">Prévisualiser HTML</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
