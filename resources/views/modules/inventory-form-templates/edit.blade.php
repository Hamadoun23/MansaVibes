<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-mansa-black leading-tight">Modifier : {{ $template->name }}</h2>
    </x-slot>

    @php
        $oldFields = old('fields');
        if ($oldFields !== null) {
            $fieldsForJs = array_values($oldFields);
        } else {
            $fieldsForJs = array_values($template->normalizedFields());
        }
    @endphp

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6">
            <form method="POST" action="{{ route('inventory-form-templates.update', $template) }}"
                  x-data="measurementTemplateBuilder(@js($fieldsForJs))"
                  class="space-y-6">
                @csrf
                @method('PATCH')

                <div>
                    <x-input-label for="name" value="Nom du modèle" />
                    <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $template->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="applies_to_stock_type" value="Pour quel type d’article ?" />
                    <select id="applies_to_stock_type" name="applies_to_stock_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="" @selected(old('applies_to_stock_type', $template->applies_to_stock_type) === null || old('applies_to_stock_type', $template->applies_to_stock_type) === '')>Tous les types</option>
                        <option value="fabric" @selected(old('applies_to_stock_type', $template->applies_to_stock_type) === 'fabric')>Tissu / matière</option>
                        <option value="accessory" @selected(old('applies_to_stock_type', $template->applies_to_stock_type) === 'accessory')>Accessoire / mercerie</option>
                        <option value="other" @selected(old('applies_to_stock_type', $template->applies_to_stock_type) === 'other')>Autre</option>
                    </select>
                    <x-input-error :messages="$errors->get('applies_to_stock_type')" class="mt-1" />
                </div>

                <div class="rounded-lg border border-gold-200 bg-white p-4 space-y-2">
                    <p class="text-sm font-medium text-mansa-black">Statut</p>
                    <label class="inline-flex items-center gap-3 cursor-pointer mt-1">
                        <input type="hidden" name="is_active" value="0" />
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-gold-600 shadow-sm focus:ring-gold-500" @checked(old('is_active', $template->is_active)) />
                        <span class="text-sm text-gray-800">Modèle <span class="font-medium">actif</span></span>
                    </label>
                </div>

                <div class="rounded-lg border border-gold-200 bg-gold-50/40 p-4 space-y-3">
                    <div>
                        <p class="text-sm font-medium text-mansa-black">Champs</p>
                        <p class="text-xs text-gray-600 mt-0.5">Les clés techniques sont conservées pour les articles déjà saisis.</p>
                    </div>
                    <x-input-error :messages="$errors->get('fields')" class="text-sm" />

                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="addRow('number')" class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-gold-500 text-mansa-black hover:bg-gold-400 border border-gold-600/20 shadow-sm">
                            + Nombre
                        </button>
                        <button type="button" @click="addRow('text')" class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-white text-gray-800 hover:bg-gray-50 border border-gray-300 shadow-sm">
                            + Texte
                        </button>
                    </div>

                    <div x-show="rows.length === 0" class="text-sm text-amber-800 py-4 text-center border border-dashed border-amber-300 rounded-md bg-amber-50/50">
                        Ajoutez au moins un champ.
                    </div>

                    <ul class="space-y-3">
                        <template x-for="(row, index) in rows" :key="row.uid">
                            <li class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                                <div class="flex justify-between items-start gap-2 mb-2">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide" x-text="'Champ ' + (index + 1)"></span>
                                    <button type="button" @click="removeRow(row.uid)" class="text-xs text-red-600 hover:text-red-800 font-medium">Retirer</button>
                                </div>
                                <input type="hidden" :name="'fields[' + index + '][key]'" x-model="row.key" />
                                <div class="space-y-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Intitulé</label>
                                        <input type="text" :name="'fields[' + index + '][label]'" x-model="row.label" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm" required />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Unité (optionnel)</label>
                                        <input type="text" :name="'fields[' + index + '][unit]'" x-model="row.unit" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Type</label>
                                        <select :name="'fields[' + index + '][type]'" x-model="row.type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                                            <option value="number">Nombre</option>
                                            <option value="text">Texte</option>
                                        </select>
                                    </div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>

                <details class="text-sm border border-gray-200 rounded-md px-3 py-2">
                    <summary class="cursor-pointer text-gray-700 font-medium">Plus d’options</summary>
                    <div class="pt-3 space-y-3">
                        <div>
                            <x-input-label for="notes" value="Notes internes (optionnel)" />
                            <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('notes', $template->notes) }}</textarea>
                        </div>
                        <div>
                            <x-input-label for="sort_order" value="Ordre d’affichage" />
                            <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="block mt-1 w-full" :value="old('sort_order', $template->sort_order)" />
                        </div>
                    </div>
                </details>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('inventory-form-templates.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Annuler</a>
                    <x-primary-button>Enregistrer</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
