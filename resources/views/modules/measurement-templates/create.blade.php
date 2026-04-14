<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-mansa-black leading-tight">Nouveau modèle de mensuration</h2>
    </x-slot>

    @php
        $oldFields = old('fields');
        if ($oldFields !== null) {
            $fieldsForJs = array_values($oldFields);
        } elseif (isset($copySource)) {
            $fieldsForJs = array_values($copySource->normalizedFields());
        } else {
            $fieldsForJs = [];
        }
        $prefillName = old('name', isset($copySource) ? 'Copie — '.$copySource->name : '');
        $prefillReferencePrice = old('reference_price_fcfa', isset($copySource) ? (int) $copySource->reference_price_fcfa : 0);
        $prefillNotes = old('notes', isset($copySource) ? (string) ($copySource->notes ?? '') : '');
        $prefillSortOrder = old('sort_order', isset($copySource) ? (int) $copySource->sort_order : 0);
        $oldActive = old('is_active');
        if ($oldActive !== null) {
            $prefillActiveBool = $oldActive === true || $oldActive === 1 || $oldActive === '1';
        } else {
            $prefillActiveBool = isset($copySource) ? (bool) $copySource->is_active : true;
        }
    @endphp

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6">
            <form
                method="POST"
                action="{{ route('measurement-templates.store') }}"
                x-data="measurementTemplateCreateForm({
                    initialRows: @js($fieldsForJs),
                    templates: @js($templatesCatalog ?? []),
                    initialCopyFromId: @js($copyFromId),
                    formName: @js($prefillName),
                    formRefPrice: @js((int) $prefillReferencePrice),
                    formNotes: @js($prefillNotes),
                    formSortOrder: @js((int) $prefillSortOrder),
                    formActive: @js($prefillActiveBool),
                })"
                class="space-y-6"
            >
                @csrf

                @if ($existingTemplates->isNotEmpty())
                    <div class="rounded-lg border border-gold-200 bg-gold-50/40 p-4 space-y-2">
                        <x-input-label for="utiliser_modele_existant" value="Utiliser un modèle existant (optionnel)" />
                        <select
                            id="utiliser_modele_existant"
                            x-model="copyFromId"
                            @change="onCopyFromChange()"
                            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-gold-500 focus:ring-gold-500"
                        >
                            <option value="">— Non, je pars de zéro —</option>
                            @foreach ($existingTemplates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-600">Remplit automatiquement les champs ci-dessous (nom, mesures, prix, options). Vous pouvez tout modifier et ajouter des mesures avant d’enregistrer un <strong>nouveau</strong> modèle.</p>
                    </div>
                @endif

                <div>
                    <x-input-label for="name" value="Comment s’appelle ce type de vêtement ?" />
                    <x-text-input
                        id="name"
                        name="name"
                        class="block mt-1 w-full"
                        x-model="formName"
                        required
                        placeholder="Ex. Robe cocktail, Grand boubou, Costume trois pièces"
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div class="rounded-lg border border-gold-200 bg-white p-4 space-y-2">
                    <p class="text-sm font-medium text-mansa-black">Statut du modèle</p>
                    <p class="text-xs text-gray-600">Désactivez un modèle pour le retirer des listes (nouvelles commandes, choix sur fiche client). Les anciennes fiches et commandes gardent leurs données.</p>
                    <label class="inline-flex items-center gap-3 cursor-pointer mt-1">
                        <input type="hidden" name="is_active" x-bind:value="formActive ? 1 : 0" />
                        <input
                            id="is_active_checkbox"
                            type="checkbox"
                            x-model="formActive"
                            class="rounded border-gray-300 text-gold-600 shadow-sm focus:ring-gold-500"
                        />
                        <span class="text-sm text-gray-800"><span class="font-medium">Modèle actif</span> — visible pour les nouvelles sélections</span>
                    </label>
                </div>

                <div class="rounded-lg border border-gold-200 bg-gold-50/40 p-4 space-y-3">
                    <div>
                        <p class="text-sm font-medium text-mansa-black">Les mesures à prendre</p>
                        <p class="text-xs text-gray-600 mt-0.5">Ajoutez une ligne par question, comme dans un formulaire en ligne.</p>
                    </div>
                    <x-input-error :messages="$errors->get('fields')" class="text-sm" />

                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="addRow('number')" class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-gold-500 text-mansa-black hover:bg-gold-400 border border-gold-600/20 shadow-sm">
                            + Mesure (nombre)
                        </button>
                        <button type="button" @click="addRow('text')" class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-white text-gray-800 hover:bg-gray-50 border border-gray-300 shadow-sm">
                            + Texte libre
                        </button>
                    </div>

                    <div x-show="rows.length === 0" class="text-sm text-gray-500 py-6 text-center border border-dashed border-gray-300 rounded-md bg-white/60">
                        Choisissez un modèle existant ci-dessus ou ajoutez une mesure avec les boutons.
                    </div>

                    <ul class="space-y-3">
                        <template x-for="(row, index) in rows" :key="row.uid">
                            <li class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                                <div class="flex justify-between items-start gap-2 mb-2">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide" x-text="'Mesure ' + (index + 1)"></span>
                                    <button type="button" @click="removeRow(row.uid)" class="text-xs text-red-600 hover:text-red-800 font-medium">Retirer</button>
                                </div>
                                <input type="hidden" :name="'fields[' + index + '][key]'" x-model="row.key" />
                                <div class="space-y-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Intitulé</label>
                                        <input
                                            type="text"
                                            :name="'fields[' + index + '][label]'"
                                            x-model="row.label"
                                            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                                            placeholder="Ex. Tour de taille, Longueur de manche"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Unité <span class="font-normal text-gray-500">(optionnel)</span></label>
                                        <input
                                            type="text"
                                            :name="'fields[' + index + '][unit]'"
                                            x-model="row.unit"
                                            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                                            placeholder="cm, m…"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Type de réponse</label>
                                        <select :name="'fields[' + index + '][type]'" x-model="row.type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                                            <option value="number">Nombre (centimètres, etc.)</option>
                                            <option value="text">Texte (remarque, détail…)</option>
                                        </select>
                                    </div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="rounded-lg border border-gray-200 p-4 space-y-2">
                    <x-input-label for="reference_price_fcfa" value="Prix de référence (FCFA)" />
                    <p class="text-xs text-gray-500">Montant entier en francs CFA (sans centimes). Préremplit le prix unitaire sur les lignes de commande lorsque ce modèle est sélectionné.</p>
                    <x-text-input
                        id="reference_price_fcfa"
                        name="reference_price_fcfa"
                        type="number"
                        min="0"
                        class="block mt-1 w-full max-w-xs"
                        x-model.number="formRefPrice"
                    />
                    <x-input-error :messages="$errors->get('reference_price_fcfa')" class="mt-1" />
                </div>

                <details class="text-sm border border-gray-200 rounded-md px-3 py-2" open>
                    <summary class="cursor-pointer text-gray-700 font-medium">Plus d’options</summary>
                    <div class="pt-3 space-y-3">
                        <div>
                            <x-input-label for="notes" value="Notes internes (optionnel)" />
                            <textarea
                                id="notes"
                                name="notes"
                                rows="2"
                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                                x-model="formNotes"
                            ></textarea>
                        </div>
                        <div>
                            <x-input-label for="sort_order" value="Ordre dans la liste" />
                            <x-text-input
                                id="sort_order"
                                name="sort_order"
                                type="number"
                                min="0"
                                class="block mt-1 w-full"
                                x-model.number="formSortOrder"
                            />
                        </div>
                    </div>
                </details>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('measurement-templates.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Annuler</a>
                    <x-primary-button>Enregistrer</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
