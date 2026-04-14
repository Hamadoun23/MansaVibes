<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">Réception de stock</h2>
            <a href="{{ route('inventory.index') }}" class="text-sm text-gold-700 hover:text-gold-900 font-medium">Retour au stock</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
            <p>Enregistrez une entrée liée à un fournisseur. Si l’article a des <strong>types ou détails</strong> paramétrés (champs nombre), cochez ceux concernés et indiquez la quantité reçue pour chacun. Sinon, une seule quantité totale suffit.</p>
        </div>
        <div
            class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6"
            x-data="{
                items: @js($itemsPayload),
                rows: [{ inventory_item_id: '', quantity: '', allocations: {}, selected: {} }],
                addRow() { this.rows.push({ inventory_item_id: '', quantity: '', allocations: {}, selected: {} }); },
                removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); },
                itemMeta(id) {
                    const n = Number(id);
                    return this.items.find(i => i.id === n);
                },
                needsKey(row) {
                    const m = this.itemMeta(row.inventory_item_id);
                    return m && Array.isArray(m.numericRows) && m.numericRows.length > 0;
                },
                initRowAllocations(row) {
                    row.allocations = {};
                    row.selected = {};
                    row.quantity = '';
                    const m = this.itemMeta(row.inventory_item_id);
                    if (m && Array.isArray(m.numericRows) && m.numericRows.length) {
                        m.numericRows.forEach((nr) => {
                            row.allocations[nr.key] = '';
                            row.selected[nr.key] = false;
                        });
                    }
                },
            }"
        >
            <form method="POST" action="{{ route('inventory.reception.store') }}" class="space-y-6">
                @csrf
                <x-input-error :messages="$errors->get('lines')" class="text-sm" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Fournisseur <span class="text-red-600">*</span></label>
                        <select name="supplier_id" required class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">— Choisir —</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}" @selected((string) old('supplier_id', $preselectedSupplierId) === (string) $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('supplier_id')" class="mt-1" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Référence (BL, facture…)</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Optionnel" />
                        <x-input-error :messages="$errors->get('reference')" class="mt-1" />
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-mansa-black">Lignes</h3>
                        <button type="button" @click="addRow()" class="text-sm font-medium text-gold-800 hover:text-gold-950">+ Ligne</button>
                    </div>
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="rounded-lg border border-gray-200 p-3 space-y-2 bg-gray-50/50">
                            <div class="flex justify-end" x-show="rows.length > 1">
                                <button type="button" @click="removeRow(index)" class="text-xs text-red-600 hover:text-red-800">Retirer</button>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Article</label>
                                <select
                                    :name="'lines[' + index + '][inventory_item_id]'"
                                    x-model="row.inventory_item_id"
                                    @change="initRowAllocations(row)"
                                    required
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                                >
                                    <option value="">— Choisir —</option>
                                    @foreach ($inventoryItems as $inv)
                                        <option value="{{ $inv->id }}">{{ $inv->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="needsKey(row)" x-cloak class="space-y-2 rounded-md border border-gold-100 bg-white p-3">
                                <p class="text-xs font-medium text-gray-700">Détail par ligne (cochez et indiquez la quantité reçue)</p>
                                <template x-for="nr in (itemMeta(row.inventory_item_id)?.numericRows || [])" :key="nr.key">
                                    <div class="flex flex-wrap items-center gap-2 py-1 border-b border-gray-100 last:border-0">
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-800 min-w-[8rem]">
                                            <input
                                                type="checkbox"
                                                class="rounded border-gray-300 text-gold-700 focus:ring-gold-500"
                                                x-model="row.selected[nr.key]"
                                                @change="if (!row.selected[nr.key]) row.allocations[nr.key] = ''"
                                            />
                                            <span x-text="nr.label"></span>
                                        </label>
                                        <div class="flex items-center gap-1 flex-1 min-w-[8rem]">
                                            <span class="text-xs text-gray-500" x-text="String(itemMeta(row.inventory_item_id)?.unit ?? '').trim() || '—'"></span>
                                            <input
                                                type="number"
                                                step="any"
                                                min="0"
                                                :name="'lines[' + index + '][allocations][' + nr.key + ']'"
                                                x-model="row.allocations[nr.key]"
                                                :disabled="!row.selected[nr.key]"
                                                placeholder="0"
                                                class="block w-full border-gray-300 rounded-md shadow-sm text-sm disabled:bg-gray-100 disabled:text-gray-400"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="!needsKey(row)">
                                <label class="block text-xs font-medium text-gray-700">Quantité reçue</label>
                                <input
                                    type="number"
                                    step="any"
                                    min="0.001"
                                    :name="'lines[' + index + '][quantity]'"
                                    x-model="row.quantity"
                                    :required="!needsKey(row)"
                                    :disabled="needsKey(row)"
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                                />
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('inventory.index') }}" class="text-sm text-gray-600 hover:text-gray-900 py-2">Annuler</a>
                    <x-primary-button>Enregistrer la réception</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
