@php
    $fmt = fn ($cents) => number_format(((int) $cents) / 100, 0, ',', ' ');
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Finance — pilotage &amp; reporting</h2>
            <div class="flex flex-wrap gap-2 text-sm">
                <a href="{{ route('finance.categories.index') }}" class="text-gold-700 hover:text-gold-900 font-medium">Catégories</a>
                <a href="{{ route('finance.cash-movements.index') }}" class="text-gold-700 hover:text-gold-900 font-medium">Caisse</a>
                <a href="{{ route('finance.fixed-assets.index') }}" class="text-gold-700 hover:text-gold-900 font-medium">Immobilisations</a>
                <a href="{{ route('staff.index') }}" class="text-gold-700 hover:text-gold-900 font-medium">Salaires équipe</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
        @endif

        <form method="get" action="{{ route('finance.index') }}" class="flex flex-wrap items-end gap-3 bg-white border border-gold-100 rounded-lg p-4 shadow-sm">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Du</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="rounded-md border-gray-300 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Au</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="rounded-md border-gray-300 text-sm" />
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-mansa-black text-white text-sm rounded-md hover:opacity-90">Actualiser</button>
        </form>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white border border-gold-100 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-500 uppercase tracking-wide">CA commandes (période)</p>
                <p class="text-2xl font-bold text-mansa-black mt-1">{{ $fmt($report['ca_orders_cents']) }} <span class="text-sm font-normal text-gray-500">FCFA</span></p>
                <p class="text-xs text-gray-500 mt-1">Commandes livrées / validées / terminées</p>
            </div>
            <div class="bg-white border border-gold-100 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Encaissements</p>
                <p class="text-2xl font-bold text-green-800 mt-1">{{ $fmt($report['encaissements_cents']) }} <span class="text-sm font-normal text-gray-500">FCFA</span></p>
                <p class="text-xs text-gray-500 mt-1">Paiements factures + entrées caisse</p>
            </div>
            <div class="bg-white border border-gold-100 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Décaissements</p>
                <p class="text-2xl font-bold text-red-800 mt-1">{{ $fmt($report['decaissements_cents']) }} <span class="text-sm font-normal text-gray-500">FCFA</span></p>
                <p class="text-xs text-gray-500 mt-1">Sorties de caisse saisies</p>
            </div>
            <div class="bg-white border border-gold-100 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Résultat trésorerie</p>
                <p class="text-2xl font-bold mt-1 {{ $report['resultat_cash_cents'] >= 0 ? 'text-green-800' : 'text-red-800' }}">{{ $fmt($report['resultat_cash_cents']) }} <span class="text-sm font-normal text-gray-500">FCFA</span></p>
                <p class="text-xs text-gray-500 mt-1">Encaissements − décaissements</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white border border-gold-100 rounded-xl p-5 shadow-sm space-y-3">
                <h3 class="font-semibold text-mansa-black">Rentabilité &amp; seuil</h3>
                <p class="text-sm text-gray-600">Marge brute estimée (paramètre) : <strong>{{ $report['margin_percent_config'] }}&nbsp;%</strong></p>
                <p class="text-sm text-gray-600">Charges fixes mensuelles (paramètre) : <strong>{{ $fmt($report['fixed_monthly_cents']) }} FCFA</strong></p>
                @if ($report['break_even_ca_cents'])
                    <p class="text-sm text-gray-800">CA mensuel cible (point mort approx.) : <strong>{{ $fmt($report['break_even_ca_cents']) }} FCFA</strong> — à atteindre pour couvrir les fixes, avec la marge indiquée.</p>
                @endif
                <p class="text-sm text-gray-600">Résultat approx. (CA commandes − dépenses caisse période) : <strong class="{{ $report['resultat_approx_cents'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $fmt($report['resultat_approx_cents']) }} FCFA</strong></p>
            </div>
            <div class="bg-white border border-gold-100 rounded-xl p-5 shadow-sm space-y-3">
                <h3 class="font-semibold text-mansa-black">Bilan simplifié (instantané)</h3>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li>Trésorerie caisse (cumul) : <strong>{{ $fmt($report['tresorerie_nette_cents']) }} FCFA</strong></li>
                    <li>Créances clients : <strong>{{ $fmt($report['creances_cents']) }} FCFA</strong></li>
                    <li>Immobilisations (brut) : <strong>{{ $fmt($report['immobilisations_brut_cents']) }} FCFA</strong></li>
                    <li class="pt-2 border-t border-gold-100">Actif estimé : <strong>{{ $fmt($report['actif_simplifie_cents']) }} FCFA</strong></li>
                    <li>Avances clients (passif) : <strong>{{ $fmt($report['passif_avances_cents']) }} FCFA</strong></li>
                    <li>Capitaux propres approx. : <strong>{{ $fmt($report['capitaux_propres_approx_cents']) }} FCFA</strong></li>
                </ul>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white border border-gold-100 rounded-xl p-5 shadow-sm">
                <h3 class="font-semibold text-mansa-black mb-3">Paramètres (seuil &amp; scénarios)</h3>
                <form method="post" action="{{ route('finance.settings.update') }}" class="space-y-3 text-sm">
                    @csrf
                    <input type="hidden" name="from" value="{{ $from->format('Y-m-d') }}" />
                    <input type="hidden" name="to" value="{{ $to->format('Y-m-d') }}" />
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Marge brute estimée (%)</label>
                        <input type="number" name="finance_margin_percent" min="0" max="95" class="w-full rounded-md border-gray-300 text-sm" value="{{ old('finance_margin_percent', $settings['finance_margin_percent'] ?? 35) }}" required />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Charges fixes mensuelles (FCFA)</label>
                        <input type="number" step="1" name="finance_fixed_monthly_fcfa" min="0" class="w-full rounded-md border-gray-300 text-sm" value="{{ old('finance_fixed_monthly_fcfa', (($settings['finance_fixed_monthly_cents'] ?? 0) / 100)) }}" required />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Hypothèse croissance CA / mois (projection, %)</label>
                        <input type="number" name="finance_risk_growth_percent" min="-50" max="100" class="w-full rounded-md border-gray-300 text-sm" value="{{ old('finance_risk_growth_percent', $settings['finance_risk_growth_percent'] ?? 0) }}" required />
                    </div>
                    <button type="submit" class="inline-flex px-4 py-2 bg-gold-500 text-mansa-black text-sm font-semibold rounded-md hover:bg-gold-400">Enregistrer</button>
                </form>
            </div>
            <div class="bg-white border border-gold-100 rounded-xl p-5 shadow-sm space-y-3">
                <h3 class="font-semibold text-mansa-black">Risque &amp; projection (3 mois)</h3>
                <p class="text-sm text-gray-600">Masse salariale déclarée : <strong>{{ $fmt($report['payroll_monthly_cents']) }} FCFA</strong> / mois</p>
                <p class="text-sm text-gray-600">« Burn » mensuel moyen (3 mois) : charges caisse − encaissements : <strong>{{ $fmt($report['burn_monthly_cents']) }} FCFA</strong></p>
                @if ($report['runway_months'] !== null)
                    <p class="text-sm text-amber-900 bg-amber-50 border border-amber-200 rounded-md p-3">Autonomie trésorerie (ordre de grandeur) : <strong>{{ $report['runway_months'] }} mois</strong> si le rythme récent se maintient.</p>
                @else
                    <p class="text-sm text-gray-500">Autonomie : non calculable (pas de burn positif ou trésorerie nulle).</p>
                @endif
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead><tr class="text-left text-gray-500"><th class="py-1 pr-2">Mois</th><th class="py-1 pr-2">CA projeté</th><th class="py-1 pr-2">Charges</th><th class="py-1">Cumul</th></tr></thead>
                        <tbody>
                            @foreach ($report['projection'] as $row)
                                <tr class="border-t border-gray-100">
                                    <td class="py-1 pr-2">{{ $row['month'] }}</td>
                                    <td class="py-1 pr-2">{{ $fmt($row['ca_projete_cents']) }}</td>
                                    <td class="py-1 pr-2">{{ $fmt($row['charges_projetees_cents']) }}</td>
                                    <td class="py-1 {{ $row['solde_cumule_cents'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $fmt($row['solde_cumule_cents']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="bg-white border border-gold-100 rounded-xl p-5 shadow-sm overflow-hidden">
                <h3 class="font-semibold text-mansa-black mb-3">Modèles les plus rentables (CA période)</h3>
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left text-gray-500 text-xs uppercase"><th class="pb-2">Modèle</th><th class="pb-2">Cmd</th><th class="pb-2 text-right">CA</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($report['top_modeles'] as $row)
                            <tr>
                                <td class="py-2 pr-2">{{ $row->model_key }}</td>
                                <td class="py-2 pr-2">{{ (int) $row->cnt }}</td>
                                <td class="py-2 text-right font-medium">{{ $fmt((int) $row->revenue_cents) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-gray-500">Aucune donnée sur la période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="bg-white border border-gold-100 rounded-xl p-5 shadow-sm overflow-hidden">
                <h3 class="font-semibold text-mansa-black mb-3">Dépenses les plus élevées (par catégorie)</h3>
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left text-gray-500 text-xs uppercase"><th class="pb-2">Catégorie</th><th class="pb-2 text-right">Montant</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($report['top_depenses_categories'] as $row)
                            <tr>
                                <td class="py-2 pr-2">{{ $row->name }}</td>
                                <td class="py-2 text-right font-medium text-red-800">{{ $fmt($row->total_cents) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-gray-500">Aucune sortie de caisse sur la période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white border border-gold-100 rounded-xl p-5 shadow-sm overflow-x-auto">
            <h3 class="font-semibold text-mansa-black mb-3">Journal des opérations (caisse + paiements factures)</h3>
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-gray-500 text-xs uppercase"><th class="pb-2">Date</th><th class="pb-2">Type</th><th class="pb-2">Libellé</th><th class="pb-2">Sens</th><th class="pb-2 text-right">Montant</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($report['journal'] as $line)
                        <tr>
                            <td class="py-2 pr-2 whitespace-nowrap">{{ $line->date }}</td>
                            <td class="py-2 pr-2">{{ $line->type === 'caisse' ? 'Caisse' : 'Paiement' }}</td>
                            <td class="py-2 pr-2">{{ $line->label }}</td>
                            <td class="py-2 pr-2">{{ $line->direction === 'in' ? 'Entrée' : 'Sortie' }}</td>
                            <td class="py-2 text-right font-mono">{{ $fmt($line->amount_cents) }}</td>
                        </tr>
                    @empty <tr><td colspan="5" class="py-4 text-gray-500">Aucune écriture sur la période.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
