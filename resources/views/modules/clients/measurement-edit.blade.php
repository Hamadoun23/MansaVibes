<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Modifier mensuration — {{ $client->name }}</h2>
            <a href="{{ route('clients.show', $client) }}" class="text-sm text-gold-700 hover:text-gold-900 font-medium">Retour fiche</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6">
                @if ($measurement->measurement_form_template_id && $measurement->measurementTemplate)
                    <form method="POST" action="{{ route('clients.measurements.update', [$client, $measurement]) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <x-input-label for="description" value="Description de cette prise" />
                            <x-text-input id="description" name="description" class="block mt-1 w-full" :value="old('description', $measurement->label)" required />
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        <p class="text-xs text-gray-600">Modèle : <strong>{{ $measurement->measurementTemplate->name }}</strong></p>

                        <div class="grid sm:grid-cols-2 gap-3">
                            @php $dataBag = is_array($measurement->data) ? $measurement->data : []; @endphp
                            @foreach ($measurement->measurementTemplate->normalizedFields() as $f)
                                @php
                                    $k = $f['key'];
                                    $val = old("field_values.$k", $dataBag[$k] ?? '');
                                @endphp
                                <div>
                                    <label class="block text-sm font-medium text-gray-700" for="fv_{{ $k }}">
                                        {{ $f['label'] }}@if ($f['unit'] !== '') ({{ $f['unit'] }}) @endif
                                    </label>
                                    @if ($f['type'] === 'number')
                                        <x-text-input id="fv_{{ $k }}" name="field_values[{{ $k }}]" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="$val" />
                                    @else
                                        <x-text-input id="fv_{{ $k }}" name="field_values[{{ $k }}]" class="block mt-1 w-full" :value="$val" />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <x-input-label for="measurement_notes" value="Notes" />
                            <textarea id="measurement_notes" name="measurement_notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('measurement_notes', $measurement->measurement_notes) }}</textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('clients.show', $client) }}" class="text-sm text-gray-600 hover:text-gray-900">Annuler</a>
                            <x-primary-button>Enregistrer</x-primary-button>
                        </div>
                    </form>
                @else
                    <form method="POST" action="{{ route('clients.measurements.update', [$client, $measurement]) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <x-input-label for="description" value="Description de cette prise" />
                            <x-text-input id="description" name="description" class="block mt-1 w-full" :value="old('description', $measurement->label)" required />
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        @if ($measurement->measurement_form_template_id && ! $measurement->measurementTemplate)
                            <p class="text-xs text-amber-700">Le modèle associé a été supprimé. Vous pouvez réenregistrer au format libre ci-dessous.</p>
                        @endif
                        <div class="grid sm:grid-cols-2 gap-3">
                            @foreach (['poitrine_cm' => 'Poitrine (cm)', 'taille_cm' => 'Taille (cm)', 'hanche_cm' => 'Hanche (cm)', 'longueur_cm' => 'Longueur (cm)', 'epaule_cm' => 'Épaule (cm)'] as $field => $flabel)
                                <div>
                                    <x-input-label :for="$field" :value="$flabel" />
                                    <x-text-input :id="$field" :name="$field" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="old($field, $measurement->{$field})" />
                                    <x-input-error :messages="$errors->get($field)" class="mt-1" />
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <x-input-label for="measurement_notes" value="Notes" />
                            <textarea id="measurement_notes" name="measurement_notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('measurement_notes', $measurement->measurement_notes) }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <p class="text-xs font-medium text-gray-600">Mesures supplémentaires</p>
                            @php $rows = old('custom_rows', $measurement->normalizedCustomMeasures()); @endphp
                            @for ($i = 0; $i < 5; $i++)
                                @php $row = $rows[$i] ?? ['label' => '', 'value' => '', 'unit' => '']; @endphp
                                <div class="grid grid-cols-12 gap-2 items-end">
                                    <div class="col-span-5">
                                        <x-input-label :for="'cr_d_'.$i" value="Description" />
                                        <x-text-input :id="'cr_d_'.$i" name="custom_rows[{{ $i }}][description]" class="block mt-1 w-full" :value="$row['label'] ?? ''" />
                                    </div>
                                    <div class="col-span-4">
                                        <x-input-label :for="'cr_v_'.$i" value="Valeur" />
                                        <x-text-input :id="'cr_v_'.$i" name="custom_rows[{{ $i }}][value]" class="block mt-1 w-full" :value="$row['value'] ?? ''" />
                                    </div>
                                    <div class="col-span-3">
                                        <x-input-label :for="'cr_u_'.$i" value="Unité" />
                                        <x-text-input :id="'cr_u_'.$i" name="custom_rows[{{ $i }}][unit]" class="block mt-1 w-full" :value="$row['unit'] ?? ''" />
                                    </div>
                                </div>
                            @endfor
                        </div>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('clients.show', $client) }}" class="text-sm text-gray-600 hover:text-gray-900">Annuler</a>
                            <x-primary-button>Enregistrer</x-primary-button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
