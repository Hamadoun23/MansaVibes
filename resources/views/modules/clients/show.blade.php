<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">{{ $client->name }}</h2>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-sm">
                @if (auth()->user()->role !== 'tailleur')
                    <a href="{{ route('orders.create', ['client_id' => $client->id]) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-gold-500 text-mansa-black font-semibold text-xs uppercase tracking-wide hover:bg-gold-400 border border-gold-600/20 shadow-sm">
                        {{ __('clients.new_order') }}
                    </a>
                @endif
                <a href="{{ route('clients.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('clients.list') }}</a>
                <a href="{{ route('clients.edit', $client) }}" class="text-gold-700 hover:text-gold-900 font-medium">{{ __('clients.edit_card') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6 grid sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">{{ __('clients.phone_label') }}</p>
                    <p class="font-medium text-mansa-black">{{ $client->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">{{ __('clients.email_label') }}</p>
                    <p class="font-medium text-mansa-black">{{ $client->email ?? '—' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-gray-500 text-xs uppercase tracking-wide">{{ __('clients.balance_label') }}</p>
                    <p class="font-semibold {{ $client->balance_cents > 0 ? 'text-amber-800' : ($client->balance_cents < 0 ? 'text-green-800' : 'text-gray-800') }}">
                        {{ number_format($client->balance_cents / 100, 0, ',', ' ') }} FCFA
                    </p>
                    <p class="text-gray-500 text-xs mt-1">{{ __('clients.balance_hint') }}</p>
                </div>
                @if ($client->notes)
                    <div class="sm:col-span-2 border-t pt-3 mt-1">
                        <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">{{ __('clients.notes_label') }}</p>
                        <p class="text-gray-800 whitespace-pre-wrap">{{ $client->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6">
                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <h3 class="font-semibold text-mansa-black">{{ __('clients.orders_history') }}</h3>
                    @if (auth()->user()->role !== 'tailleur')
                        <a href="{{ route('orders.create', ['client_id' => $client->id]) }}" class="text-sm font-medium text-gold-800 hover:text-gold-950 underline decoration-gold-600/50">{{ __('clients.add_order') }}</a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs text-gray-500 uppercase">Réf.</th>
                                <th class="px-3 py-2 text-left text-xs text-gray-500 uppercase">Modèle</th>
                                <th class="px-3 py-2 text-left text-xs text-gray-500 uppercase">Statut</th>
                                <th class="px-3 py-2 text-left text-xs text-gray-500 uppercase">Mode</th>
                                <th class="px-3 py-2 text-right text-xs text-gray-500 uppercase">Total</th>
                                <th class="px-3 py-2 text-right text-xs text-gray-500 uppercase">Reste</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($client->orders as $order)
                                <tr>
                                    <td class="px-3 py-2 font-mono">{{ $order->reference }}</td>
                                    <td class="px-3 py-2">{{ $order->displayModelLabel() }}</td>
                                    <td class="px-3 py-2">{{ $order->statusLabel() }}</td>
                                    <td class="px-3 py-2">{{ $order->deliveryModeLabel() }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format($order->total_cents / 100, 0, ',', ' ') }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format($order->balanceDueCents() / 100, 0, ',', ' ') }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('orders.show', $order) }}" class="text-gold-700 hover:text-gold-900">Voir</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">{{ __('clients.no_orders') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @php
                $templatesJson = $templates->map(fn ($t) => [
                    'id' => (string) $t->id,
                    'name' => $t->name,
                    'fields' => $t->normalizedFields(),
                ])->values();
            @endphp

            <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6 space-y-6">
                <div class="flex flex-wrap justify-between items-center gap-2">
                    <h3 class="font-semibold text-mansa-black">{{ __('clients.measurements_title') }}</h3>
                    @if (auth()->user()->role !== 'tailleur')
                        <a href="{{ route('measurement-templates.index') }}" class="text-xs text-gold-700 hover:text-gold-900 font-medium">{{ __('clients.configure_templates') }}</a>
                    @endif
                </div>

                <div
                    class="rounded-lg border border-dashed border-gold-200 p-4 bg-gold-50/30"
                    x-data="{
                        templateId: @js((string) old('measurement_form_template_id', (string) ($templates->first()?->id ?? ''))),
                        templates: @js($templatesJson),
                        get currentFields() {
                            const t = this.templates.find(x => String(x.id) === String(this.templateId));
                            return t ? t.fields : [];
                        },
                        pickTemplate(id) {
                            this.templateId = String(id);
                            this.$nextTick(() => this.$el.scrollIntoView({ behavior: 'smooth', block: 'start' }));
                        }
                    }"
                >
                    <p class="text-sm font-medium text-mansa-black mb-1">{{ __('clients.new_measurement_title') }}</p>
                    <p class="text-xs text-gray-600 mb-4">{{ __('clients.new_measurement_help') }}</p>

                    @if ($templates->isNotEmpty())
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach ($templates as $tpl)
                                @php
                                    $tplMeasureCount = (int) ($measurementCountByTemplate[$tpl->id] ?? 0);
                                @endphp
                                <button
                                    type="button"
                                    @click="pickTemplate('{{ $tpl->id }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm transition"
                                    x-bind:class="String(templateId) === '{{ (string) $tpl->id }}'
                                        ? 'border-gold-600 bg-gold-200/60 text-mansa-black font-medium shadow-sm'
                                        : 'border-gray-300 bg-white text-gray-800 hover:border-gold-400'"
                                >
                                    <span>{{ $tpl->name }}</span>
                                    @if ($tplMeasureCount > 0)
                                        <span class="text-xs font-normal opacity-80">({{ $tplMeasureCount }} fiche{{ $tplMeasureCount > 1 ? 's' : '' }})</span>
                                    @else
                                        <span class="text-xs font-normal text-amber-800">Nouveau</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        <form method="POST" action="{{ route('clients.measurements.store', $client) }}" class="space-y-3">
                            @csrf

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div class="sm:col-span-2">
                                    <x-input-label value="Modèle de vêtement (formulaire)" />
                                    <select name="measurement_form_template_id" x-model="templateId" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                                        @foreach ($templates as $tpl)
                                            <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Le menu reflète le même choix que les boutons ci-dessus.</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="description" value="Description de cette prise" />
                                    <x-text-input id="description" name="description" class="block mt-1 w-full" :value="old('description')" required placeholder="Ex. Essayage du 12/03, 2e passe robe rouge" />
                                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <template x-for="f in currentFields" :key="f.key">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">
                                            <span x-text="f.label"></span>
                                            <span x-show="f.unit" class="text-gray-500" x-text="' (' + f.unit + ')'"></span>
                                        </label>
                                        <input
                                            :type="f.type === 'number' ? 'number' : 'text'"
                                            :name="'field_values[' + f.key + ']'"
                                            x-bind:step="f.type === 'number' ? '0.01' : false"
                                            x-bind:min="f.type === 'number' ? '0' : false"
                                            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                                        />
                                    </div>
                                </template>
                            </div>

                            <div>
                                <x-input-label for="measurement_notes" value="Notes" />
                                <textarea id="measurement_notes" name="measurement_notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('measurement_notes') }}</textarea>
                            </div>
                            <x-primary-button>Enregistrer les mesures</x-primary-button>
                        </form>
                    @else
                        @if (auth()->user()->role === 'tailleur')
                            <p class="text-sm text-amber-800 mb-3">{{ __('clients.ask_owner_templates') }}</p>
                        @else
                            <p class="text-sm text-amber-800 mb-3">{{ __('clients.no_templates_owner') }}</p>
                            <a href="{{ route('measurement-templates.create') }}" class="text-sm text-gold-700 font-medium">{{ __('clients.create_template') }}</a>
                        @endif
                    @endif

                    @if ($templates->isNotEmpty())
                        <details class="mt-6 text-xs text-gray-600 border-t border-gold-200/60 pt-4">
                            <summary class="cursor-pointer text-gold-800 font-medium">Formulaire libre (ancien format, sans modèle)</summary>
                            <form method="POST" action="{{ route('clients.measurements.store', $client) }}" class="space-y-3 mt-3">
                                @csrf
                                <div class="grid sm:grid-cols-2 gap-3">
                                    <div class="sm:col-span-2">
                                        <x-input-label for="description_legacy" value="Description de cette prise" />
                                        <x-text-input id="description_legacy" name="description" class="block mt-1 w-full" :value="old('description')" />
                                    </div>
                                    @foreach (['poitrine_cm' => 'Poitrine (cm)', 'taille_cm' => 'Taille (cm)', 'hanche_cm' => 'Hanche (cm)', 'longueur_cm' => 'Longueur (cm)', 'epaule_cm' => 'Épaule (cm)'] as $field => $flabel)
                                        <div>
                                            <x-input-label :for="'leg_'.$field" :value="$flabel" />
                                            <x-text-input :id="'leg_'.$field" :name="$field" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="old($field)" />
                                        </div>
                                    @endforeach
                                </div>
                                <div>
                                    <x-input-label for="measurement_notes_leg" value="Notes" />
                                    <textarea id="measurement_notes_leg" name="measurement_notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('measurement_notes') }}</textarea>
                                </div>
                                <div class="space-y-2">
                                    <p class="text-xs font-medium text-gray-600">Mesures supplémentaires</p>
                                    @for ($i = 0; $i < 3; $i++)
                                        <div class="grid grid-cols-12 gap-2 items-end">
                                            <div class="col-span-5">
                                                <x-input-label :for="'cr_d_'.$i" value="Description" />
                                                <x-text-input :id="'cr_d_'.$i" :name="'custom_rows['.$i.'][description]'" class="block mt-1 w-full" :value="old('custom_rows.'.$i.'.description')" />
                                            </div>
                                            <div class="col-span-4">
                                                <x-input-label :for="'cr_v_'.$i" value="Valeur" />
                                                <x-text-input :id="'cr_v_'.$i" :name="'custom_rows['.$i.'][value]'" class="block mt-1 w-full" :value="old('custom_rows.'.$i.'.value')" />
                                            </div>
                                            <div class="col-span-3">
                                                <x-input-label :for="'cr_u_'.$i" value="Unité" />
                                                <x-text-input :id="'cr_u_'.$i" :name="'custom_rows['.$i.'][unit]'" class="block mt-1 w-full" placeholder="cm" :value="old('custom_rows.'.$i.'.unit')" />
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-xs font-semibold text-gray-700 uppercase">Enregistrer (format libre)</button>
                            </form>
                        </details>
                    @endif
                </div>

                <div class="space-y-4">
                    @forelse ($client->measurements as $m)
                        <div class="border border-gray-200 rounded-lg p-4 text-sm">
                            <div class="flex justify-between gap-2 flex-wrap mb-2">
                                <div>
                                    <p class="font-semibold text-mansa-black">{{ $m->label }}</p>
                                    @if ($m->isTemplateBased() && $m->measurementTemplate)
                                        <p class="text-xs text-gold-800">Modèle : {{ $m->measurementTemplate->name }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">{{ $m->created_at?->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="flex gap-2 text-xs">
                                    <a href="{{ route('clients.measurements.edit', [$client, $m]) }}" class="text-gold-700 font-medium">Modifier</a>
                                    <form action="{{ route('clients.measurements.destroy', [$client, $m]) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette fiche de mensurations ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                            <ul class="grid sm:grid-cols-2 gap-x-4 gap-y-1 text-xs">
                                @foreach ($m->displayFieldRows() as $row)
                                    <li><span class="text-gray-500">{{ $row['label'] }} :</span> <span class="font-medium">{{ $row['value'] }}</span> @if(($row['unit'] ?? '') !== '') <span class="text-gray-500">{{ $row['unit'] }}</span> @endif</li>
                                @endforeach
                            </ul>
                            @if (! $m->isTemplateBased() && $m->normalizedCustomMeasures())
                                <ul class="mt-2 text-xs text-gray-700 space-y-0.5">
                                    @foreach ($m->normalizedCustomMeasures() as $row)
                                        <li><span class="text-gray-500">{{ $row['label'] }} :</span> {{ $row['value'] }} @if(($row['unit'] ?? '') !== '') {{ $row['unit'] }} @endif</li>
                                    @endforeach
                                </ul>
                            @endif
                            @if ($m->measurement_notes)
                                <p class="mt-2 text-gray-700 whitespace-pre-wrap border-t pt-2"><span class="text-gray-500">Notes :</span> {{ $m->measurement_notes }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Aucune mensuration enregistrée.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
