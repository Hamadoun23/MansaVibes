@php
    /** @var \App\Models\InventoryItem $item */
@endphp

<div
    class="space-y-4"
    x-data="inventoryItemSchemaLines({
        initialRows: @js($schemaLinesForJs ?? []),
    })"
>
    <div class="rounded-lg border border-gold-200 bg-gold-50/30 p-4 space-y-3">
        <div>
            <p class="text-sm font-medium text-mansa-black">Intitulés des champs</p>
            <p class="text-xs text-gray-600 mt-0.5">Définissez les libellés (ex. couleur verte) et le type : <strong>nombre</strong> pour des quantités en {{ $item->unit }}, <strong>texte</strong> pour du libre. Les valeurs se saisissent ensuite dans <strong>Actualiser</strong>.</p>
        </div>
        <x-input-error :messages="$errors->get('schema_lines')" class="text-sm" />

        <div class="flex flex-wrap gap-2">
            <button type="button" @click="addRow('text')" class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-white text-gray-800 hover:bg-gray-50 border border-gray-300 shadow-sm">
                + Champ texte
            </button>
            <button type="button" @click="addRow('number')" class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-gold-500 text-mansa-black hover:bg-gold-400 border border-gold-600/20 shadow-sm">
                + Champ nombre
            </button>
        </div>

        <div x-show="rows.length === 0" class="text-sm text-gray-500 py-4 text-center border border-dashed border-gray-300 rounded-md bg-white/70">
            Aucun champ. Ajoutez au moins un intitulé pour pouvoir actualiser le stock.
        </div>

        <ul class="space-y-3">
            <template x-for="(row, index) in rows" :key="row.uid">
                <li class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm space-y-2">
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide" x-text="'Champ ' + (index + 1)"></span>
                        <button type="button" @click="removeRow(row.uid)" class="text-xs text-red-600 hover:text-red-800 font-medium">Retirer</button>
                    </div>
                    <input type="hidden" :name="'schema_lines[' + index + '][key]'" x-model="row.key" />
                    <div class="grid gap-2 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Intitulé</label>
                            <input
                                type="text"
                                :name="'schema_lines[' + index + '][label]'"
                                x-model="row.label"
                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                                placeholder="ex. couleur verte"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Type</label>
                            <select :name="'schema_lines[' + index + '][type]'" x-model="row.type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="text">Texte (lettres)</option>
                                <option value="number">Nombre (quantité en {{ $item->unit }})</option>
                            </select>
                        </div>
                    </div>
                </li>
            </template>
        </ul>
    </div>
</div>
