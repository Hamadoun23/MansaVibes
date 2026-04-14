<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-mansa-black leading-tight">Nouvelle commande</h2>
    </x-slot>

    @php
        $templatePriceMap = [];
        $templateNamesMap = [];
        foreach ($measurementTemplates as $tpl) {
            $templatePriceMap[(string) $tpl->id] = (int) ($tpl->reference_price_fcfa ?? 0);
            $templateNamesMap[(string) $tpl->id] = $tpl->name;
        }
        $templateIdsOrder = $measurementTemplates->map(fn ($t) => (string) $t->id)->values()->all();
        $oldItems = old('items');
        if (is_array($oldItems) && $oldItems !== []) {
            $initialLines = collect($oldItems)->values()->map(function (array $r) {
                return [
                    'measurement_form_template_id' => (string) ($r['measurement_form_template_id'] ?? ''),
                    'description' => (string) ($r['description'] ?? ''),
                    'quantity' => (int) ($r['quantity'] ?? 1),
                    'apply_discount' => filter_var($r['apply_discount'] ?? false, FILTER_VALIDATE_BOOLEAN)
                        || (($r['apply_discount'] ?? '') === '1'),
                    'client_supplies_fabric' => filter_var($r['client_supplies_fabric'] ?? false, FILTER_VALIDATE_BOOLEAN)
                        || (($r['client_supplies_fabric'] ?? '') === '1'),
                    'fabric_price_fcfa' => (int) ($r['fabric_price_fcfa'] ?? 0),
                    'inventory_item_id' => (string) ($r['inventory_item_id'] ?? ''),
                    'inventory_characteristic_key' => (string) ($r['inventory_characteristic_key'] ?? ''),
                    'inventory_consumed_meters' => isset($r['inventory_consumed_meters']) && $r['inventory_consumed_meters'] !== ''
                        ? (string) $r['inventory_consumed_meters']
                        : '',
                ];
            })->all();
        } else {
            $initialLines = [];
        }
        $initialDiscountScope = old('discount_scope', 'none');
        if ($initialDiscountScope === 'order') {
            $initialDiscountScope = 'all';
        }
    @endphp

    <div class="py-8">
        <div
            class="max-w-5xl mx-auto sm:px-6 lg:px-8"
            x-data="orderFormLines({
                templatePrices: @js($templatePriceMap),
                templateNames: @js($templateNamesMap),
                templateIdsOrder: @js($templateIdsOrder),
                initialLines: @js($initialLines),
                discountScope: @js($initialDiscountScope),
                discountPercent: @js((int) old('discount_percent', 0)),
                formAdvanceFcfa: @js((int) round((int) old('advance_payment_cents', 0) / 100)),
                inventoryOptions: @js($inventoryOptions),
            })"
        >
            <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6">
                <form method="POST" action="{{ route('orders.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="client_id" value="Client" />
                        <select id="client_id" name="client_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">—</option>
                            @foreach ($clients as $c)
                                <option value="{{ $c->id }}" @selected(old('client_id', $prefillClientId) == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                    </div>

                    @if ($measurementTemplates->isNotEmpty())
                        <div>
                            <x-input-label value="Modèles / types de pièce (un ou plusieurs)" />
                            <fieldset class="mt-2 border border-gray-200 rounded-md p-3 bg-gray-50/50 space-y-0">
                                <legend class="sr-only">Choix des modèles</legend>
                                <div class="grid sm:grid-cols-2 gap-2 max-h-52 overflow-y-auto pr-1">
                                    @foreach ($measurementTemplates as $tpl)
                                        <label class="flex items-start gap-2.5 text-sm text-gray-800 cursor-pointer rounded-md border border-transparent hover:border-gold-200 hover:bg-white px-2 py-2">
                                            <input
                                                type="checkbox"
                                                class="mt-0.5 rounded border-gray-300 text-gold-600 focus:ring-gold-500 shrink-0"
                                                x-bind:checked="globalTemplateIds.includes('{{ (string) $tpl->id }}')"
                                                @change="toggleGlobalTemplate('{{ (string) $tpl->id }}', $event.target.checked)"
                                            />
                                            <span>{{ $tpl->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <p class="text-xs text-gray-500 mt-1">Cochez un ou plusieurs modèles. Le prix affiché pour chaque ligne correspond au <strong>prix de référence</strong> du modèle (Mensuration).</p>
                        </div>
                    @else
                        <p class="text-sm text-amber-800">Aucun modèle actif. <a href="{{ route('measurement-templates.create') }}" class="underline text-gold-800">Créer un modèle</a></p>
                    @endif

                    <div>
                        <x-input-label for="model_notes" value="Notes sur le(s) modèle(s)" />
                        <textarea id="model_notes" name="model_notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('model_notes') }}</textarea>
                    </div>

                    <fieldset class="border border-gold-200 rounded-md p-4 space-y-3 bg-gold-50/30">
                        <legend class="text-sm font-medium text-mansa-black px-1">Lignes — prix atelier ou tissu client</legend>
                        <x-input-error :messages="$errors->get('items')" class="text-sm" />
                        <div class="flex flex-wrap gap-2 items-center justify-between">
                            <p class="text-xs text-gray-600">
                                Total estimé : <span class="font-semibold text-mansa-black"><span x-text="Math.round(estimatedTotal() / 100).toLocaleString('fr-FR')"></span> FCFA</span>
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="setClientFabricForAll()" class="text-xs px-2 py-1 rounded border border-gold-300 bg-white text-mansa-black hover:bg-gold-50">
                                    Tissu client : tout cocher
                                </button>
                                <button type="button" @click="clearClientFabricForAll()" class="text-xs px-2 py-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                    Tissu client : tout décocher
                                </button>
                            </div>
                        </div>
                        <div class="overflow-x-auto border border-gray-200 rounded-md bg-white">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-2 py-2 text-left">Modèle</th>
                                        <th class="px-2 py-2 text-left">Description</th>
                                        <th class="px-2 py-2 text-left min-w-[11rem]">Article stock</th>
                                        <th class="px-2 py-2 text-left w-20">Qté</th>
                                        <th class="px-2 py-2 text-left w-28">Tissu client</th>
                                        <th class="px-2 py-2 text-left min-w-[8rem]">Prix (FCFA)</th>
                                        <th class="px-2 py-2 text-left w-28" x-show="discountScope === 'lines'" x-cloak>Remise</th>
                                        <th class="px-2 py-2 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <template x-for="(line, index) in lines" :key="line.uid">
                                        <tr class="align-top">
                                            <td class="px-2 py-2">
                                                <input type="hidden" :name="'items[' + index + '][measurement_form_template_id]'" x-bind:value="line.measurement_form_template_id" />
                                                <span class="text-xs text-gray-800 font-medium" x-text="templateNames[line.measurement_form_template_id] || '—'"></span>
                                            </td>
                                            <td class="px-2 py-2">
                                                <input type="text" class="w-full border-gray-300 rounded-md shadow-sm text-sm" x-model="line.description" :name="'items[' + index + '][description]'" />
                                            </td>
                                            <td class="px-2 py-2">
                                                <select :name="'items[' + index + '][inventory_item_id]'" x-model="line.inventory_item_id" @change="line.inventory_characteristic_key = ''; line.inventory_consumed_meters = ''" class="w-full max-w-[14rem] border-gray-300 rounded-md shadow-sm text-xs">
                                                    <option value="">—</option>
                                                    <template x-for="opt in inventoryOptions" :key="opt.id">
                                                        <option :value="opt.id" x-text="opt.name"></option>
                                                    </template>
                                                </select>
                                                <div x-show="needsFabricStock(line)" x-cloak class="mt-2 space-y-1">
                                                    <label class="block text-[10px] font-medium text-gray-600">Ligne de stock (type / détail)</label>
                                                    <select :name="'items[' + index + '][inventory_characteristic_key]'" x-model="line.inventory_characteristic_key" class="w-full max-w-[14rem] border-gray-300 rounded-md shadow-sm text-xs">
                                                        <option value="">—</option>
                                                        <template x-for="nr in (inventoryMeta(line.inventory_item_id)?.numericRows || [])" :key="nr.key">
                                                            <option :value="nr.key" x-text="nr.label"></option>
                                                        </template>
                                                    </select>
                                                    <label class="block text-[10px] font-medium text-gray-600">
                                                        <span>Quantité à prélever</span>
                                                        <span class="text-gray-500 font-normal" x-show="inventoryMeta(line.inventory_item_id)?.unit" x-text="' (' + inventoryMeta(line.inventory_item_id).unit + ')'"></span>
                                                    </label>
                                                    <input type="number" step="any" min="0.001" :name="'items[' + index + '][inventory_consumed_meters]'" x-model="line.inventory_consumed_meters" class="w-full max-w-[14rem] border-gray-300 rounded-md shadow-sm text-xs" placeholder="ex. 3,5" />
                                                </div>
                                                <p class="text-[10px] text-gray-500 mt-0.5">Déduction stock</p>
                                            </td>
                                            <td class="px-2 py-2">
                                                <input type="number" min="1" class="w-full border-gray-300 rounded-md shadow-sm text-sm" x-model.number="line.quantity" :name="'items[' + index + '][quantity]'" />
                                            </td>
                                            <td class="px-2 py-2">
                                                <input type="hidden" :name="'items[' + index + '][client_supplies_fabric]'" x-bind:value="line.client_supplies_fabric ? 1 : 0" />
                                                <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                                    <input type="checkbox" class="rounded border-gray-300" x-model="line.client_supplies_fabric" @change="fabricToggle(line)" />
                                                    <span class="hidden sm:inline">Apporte le tissu</span>
                                                </label>
                                            </td>
                                            <td class="px-2 py-2 text-sm">
                                                <input
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                                                    :class="line.client_supplies_fabric ? '' : 'bg-gray-50'"
                                                    x-model.number="line.fabric_price_fcfa"
                                                    :name="'items[' + index + '][fabric_price_fcfa]'"
                                                    :readonly="!line.client_supplies_fabric"
                                                />
                                                <p class="text-[10px] text-gray-500 mt-0.5" x-show="!line.client_supplies_fabric">Prix réf. mensuration (non modifiable)</p>
                                                <p class="text-[10px] text-amber-800 mt-0.5" x-show="line.client_supplies_fabric">Tissu client — prix libre</p>
                                            </td>
                                            <td class="px-2 py-2" x-show="discountScope === 'lines'" x-cloak>
                                                <input type="hidden" :name="'items[' + index + '][apply_discount]'" x-bind:value="line.apply_discount ? 1 : 0" />
                                                <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
                                                    <input type="checkbox" x-model="line.apply_discount" class="rounded border-gray-300" />
                                                    <span>Appliquer <span x-text="discountPercent || 0"></span>%</span>
                                                </label>
                                            </td>
                                            <td class="px-2 py-2 text-right">
                                                <button type="button" @click="removeLine(line.uid)" class="text-red-600 text-xs font-medium hover:text-red-800" title="Retirer ce modèle">×</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-gray-500" x-show="lines.length === 0">Sélectionnez au moins un modèle ci-dessus.</p>
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-md p-4 space-y-3">
                        <legend class="text-sm font-medium text-gray-700 px-1">Réduction</legend>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="discount_percent" value="Pourcentage de réduction (0–100)" />
                                <input
                                    id="discount_percent"
                                    name="discount_percent"
                                    type="number"
                                    min="0"
                                    max="100"
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"
                                    x-model.number="discountPercent"
                                />
                                <x-input-error :messages="$errors->get('discount_percent')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="discount_scope" value="Portée de la réduction" />
                                <select id="discount_scope" name="discount_scope" x-model="discountScope" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="none">Aucune remise (ignorer le %)</option>
                                    <option value="all">Sur toute la commande (tous les modèles)</option>
                                    <option value="lines">Uniquement sur les modèles cochés dans le tableau</option>
                                </select>
                                <x-input-error :messages="$errors->get('discount_scope')" class="mt-1" />
                            </div>
                        </div>
                        <p class="text-xs text-gray-600">Ex. 10 ou 20 pour 10&nbsp;% ou 20&nbsp;%.</p>
                    </fieldset>

                    <div>
                        <x-input-label for="advance_payment_fcfa" value="Montant versé maintenant (FCFA)" />
                        <input type="hidden" name="advance_payment_cents" x-bind:value="Math.round((Number(formAdvanceFcfa) || 0) * 100)" />
                        <input
                            id="advance_payment_fcfa"
                            type="number"
                            min="0"
                            step="1"
                            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"
                            x-model.number="formAdvanceFcfa"
                        />
                        <p class="text-xs text-gray-500 mt-1">Doit rester <strong>inférieur au total</strong> pour un acompte, ou <strong>égal au total</strong> pour un paiement intégral (affiché « Payé » sur la fiche). Ne peut pas dépasser le total.</p>
                        <p class="text-xs text-amber-700 mt-1" x-show="estimatedTotal() > 0 && Math.round((Number(formAdvanceFcfa) || 0) * 100) > estimatedTotal()">
                            Ce montant dépasse le total estimé — corrigez avant d’enregistrer.
                        </p>
                        <p class="text-xs text-green-700 font-medium mt-1" x-show="estimatedTotal() > 0 && Math.round((Number(formAdvanceFcfa) || 0) * 100) === estimatedTotal() && (Number(formAdvanceFcfa) || 0) > 0">
                            Paiement intégral — statut paiement : Payé
                        </p>
                        <p class="text-xs text-gray-600 mt-1" x-show="estimatedTotal() > 0 && Math.round((Number(formAdvanceFcfa) || 0) * 100) < estimatedTotal() && (Number(formAdvanceFcfa) || 0) > 0">
                            Acompte partiel — il restera un solde à payer.
                        </p>
                        <x-input-error :messages="$errors->get('advance_payment_cents')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="payment_method" value="Mode de paiement" />
                        <select
                            id="payment_method"
                            name="payment_method"
                            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                            x-bind:required="(Number(formAdvanceFcfa) || 0) > 0"
                        >
                            <option value="">— Si aucun versement (0 FCFA)</option>
                            @foreach (\App\Models\Order::paymentMethodLabels() as $val => $label)
                                <option value="{{ $val }}" @selected(old('payment_method') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Obligatoire dès qu’un montant est versé.</p>
                        <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                    </div>

                    <fieldset class="border border-gray-200 rounded-md p-4">
                        <legend class="text-sm font-medium text-gray-700 px-1">Livraison</legend>
                        <div class="space-y-2 text-sm">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="delivery_mode" value="pickup" class="border-gray-300 text-gold-600 focus:ring-gold-500" @checked(old('delivery_mode', 'pickup') === 'pickup') />
                                <span>{{ \App\Models\Order::deliveryModeLabels()['pickup'] }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="delivery_mode" value="delivery" class="border-gray-300 text-gold-600 focus:ring-gold-500" @checked(old('delivery_mode') === 'delivery') />
                                <span>{{ \App\Models\Order::deliveryModeLabels()['delivery'] }}</span>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('delivery_mode')" class="mt-2" />
                    </fieldset>

                    <p class="text-xs text-gray-500 rounded-md bg-gray-50 border border-gray-100 px-3 py-2">La commande est créée avec le statut <strong>En cours</strong>. Vous pourrez passer à <strong>Livré</strong> depuis la liste ou la fiche commande.</p>
                    <div>
                        <x-input-label for="assigned_to" value="Assigné à (employé)" />
                        <select id="assigned_to" name="assigned_to" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">—</option>
                            @foreach ($employees as $e)
                                <option value="{{ $e->id }}" @selected(old('assigned_to') == $e->id)>{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="notes" value="Notes" />
                        <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Annuler</a>
                        <x-primary-button>Créer</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
