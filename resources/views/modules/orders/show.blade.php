<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">{{ __('order.show.title') }} {{ $order->reference }}</h2>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('orders.pdf', $order) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-mansa-black text-white hover:opacity-90 border border-gray-800"
                >{{ __('order.show.invoice_pdf') }}</a>
                @if (auth()->user()->role !== 'tailleur')
                    <a href="{{ route('orders.edit', $order) }}" class="text-sm text-gold-700 hover:text-gold-900 font-medium">{{ __('order.show.edit') }}</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->has('inventory'))
                <div class="rounded-md bg-red-50 p-4 text-red-800 text-sm">{{ $errors->first('inventory') }}</div>
            @endif
            @if ($errors->has('whatsapp_cloud'))
                <div class="rounded-md bg-red-50 p-4 text-red-800 text-sm">{{ $errors->first('whatsapp_cloud') }}</div>
            @endif

            @php
                $waDigits = $order->clientWhatsAppDigits();
                $isTailor = auth()->user()->role === 'tailleur';
                $emp = auth()->user()->employee;
                $canTailorValidate = $isTailor && $emp && (int) $order->assigned_to === (int) $emp->id && ! in_array($order->status, ['validated', 'delivered'], true);
            @endphp

            @if ($canTailorValidate)
                <div class="rounded-lg border border-green-600/40 bg-green-50 p-4 shadow-sm space-y-3">
                    <p class="text-sm text-gray-800 font-medium">{{ __('order.show.validate_garment') }}</p>
                    <p class="text-xs text-gray-600">{{ __('order.show.validate_help') }}</p>
                    <form method="POST" action="{{ route('tailor.orders.validate', $order) }}" onsubmit="return confirm(@json(__('tailor.confirm_validate')));">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium bg-green-700 text-white hover:bg-green-800">
                            {{ __('tailor.mark_validated') }}
                        </button>
                    </form>
                </div>
            @endif

            @unless ($isTailor)
            <div class="rounded-lg border-2 border-[#25D366]/50 bg-green-50/60 p-4 shadow-sm space-y-3">
                <h3 class="font-semibold text-mansa-black text-sm">Envoyer la facture PDF par WhatsApp</h3>

                @if ($whatsappCloudReady)
                    <p class="text-sm text-gray-800">Les boutons ci-dessous envoient le <strong>fichier PDF en pièce jointe</strong> sur le WhatsApp du client (API WhatsApp Cloud Meta).</p>
                    <div class="flex flex-wrap gap-2 items-center">
                        <a
                            href="{{ route('orders.pdf', $order) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md text-sm font-medium bg-mansa-black text-white hover:opacity-90"
                        >Voir / télécharger le PDF</a>

                        @if ($waDigits)
                            <form method="POST" action="{{ route('orders.whatsapp.invoice', $order) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md text-sm font-medium bg-[#25D366] text-white hover:opacity-95">
                                    Envoyer le PDF au client (WhatsApp)
                                </button>
                            </form>
                            <form method="POST" action="{{ route('orders.whatsapp.receipt', $order) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md text-sm font-medium border-2 border-[#25D366] text-[#128C7E] bg-white hover:bg-green-50">
                                    Envoyer reçu (PDF + légende paiement)
                                </button>
                            </form>
                        @elseif ($order->client)
                            <p class="text-sm text-amber-900 w-full">
                                <a href="{{ route('clients.edit', $order->client) }}" class="underline font-medium text-gold-800">Ajoutez un numéro de mobile au client</a> (indicatif pays, sans +, ex. 22370123456) pour envoyer le PDF.
                            </p>
                        @endif
                    </div>
                @else
                    <div class="text-sm text-amber-950 bg-amber-50 border border-amber-300 rounded-md p-4 space-y-2">
                        <p><strong>Pour envoyer le PDF directement au client,</strong> vous devez configurer l’<strong>API WhatsApp Cloud</strong> (Meta). Sans cette API, aucun bouton « envoyer sur WhatsApp » ne peut joindre un fichier : ce n’est pas une limite de l’application, c’est ainsi que fonctionne WhatsApp.</p>
                        <p class="text-xs font-medium text-gray-800">À mettre dans votre fichier <code class="bg-white px-1 rounded border">.env</code> (voir aussi <code class="bg-white px-1 rounded border">.env.example</code>) :</p>
                        <ul class="text-xs text-gray-700 list-disc list-inside space-y-0.5 font-mono">
                            <li>WHATSAPP_CLOUD_ENABLED=true</li>
                            <li>WHATSAPP_CLOUD_ACCESS_TOKEN=…</li>
                            <li>WHATSAPP_CLOUD_PHONE_NUMBER_ID=…</li>
                        </ul>
                        <p class="text-xs text-gray-600 pt-1">
                            <a href="https://developers.facebook.com/docs/whatsapp/cloud-api" target="_blank" rel="noopener noreferrer" class="text-gold-800 font-medium underline">Guide officiel Meta — WhatsApp Cloud API</a>
                        </p>
                    </div>
                    <div>
                        <a
                            href="{{ route('orders.pdf', $order) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md text-sm font-medium bg-mansa-black text-white hover:opacity-90"
                        >Télécharger la facture PDF</a>
                        <p class="text-xs text-gray-500 mt-2">Après configuration de l’API, les boutons d’envoi WhatsApp (pièce jointe PDF) apparaîtront ici.</p>
                    </div>
                @endif
            </div>
            @endunless

            <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6 grid gap-2 text-sm">
                <p><span class="text-gray-500">{{ __('order.show.client') }}</span> <span class="font-medium">{{ $order->client?->name }}</span> @if($order->client) <a href="{{ route('clients.show', $order->client) }}" class="text-gold-700 text-xs ml-1">{{ __('order.show.crm_link') }}</a> @endif</p>
                @if ($order->measurement_form_template_id || filled($order->model_name))
                    <p><span class="text-gray-500">{{ __('order.show.model') }}</span> <span class="font-medium">{{ $order->displayModelLabel() }}</span></p>
                @endif
                <p><span class="text-gray-500">{{ __('order.show.status') }}</span> {{ $order->statusLabel() }}</p>
                <p><span class="text-gray-500">{{ __('order.show.delivery') }}</span> {{ $order->deliveryModeLabel() }}</p>
                <p><span class="text-gray-500">{{ __('order.show.assigned') }}</span> {{ $order->assignee?->name ?? '—' }}</p>
                @php
                    $grossCents = $order->subtotalGrossCents();
                    $lineDiscCents = (int) $order->items->sum(fn ($i) => $i->effectiveLineDiscountCents());
                    $orderDiscCents = (int) ($order->order_discount_cents ?? 0);
                    $discScope = $order->discount_scope === 'order' ? 'all' : $order->discount_scope;
                    $discPct = (int) ($order->discount_percent ?? 0);
                @endphp
                <p><span class="text-gray-500">Sous-total (brut) :</span> {{ number_format($grossCents / 100, 0, ',', ' ') }} FCFA</p>
                @if ($discScope === 'all' && $discPct > 0 && $orderDiscCents > 0)
                    <p><span class="text-gray-500">Remise commande ({{ $discPct }}&nbsp;%) :</span> −{{ number_format($orderDiscCents / 100, 0, ',', ' ') }} FCFA</p>
                @elseif ($discScope === 'lines' && $discPct > 0 && $lineDiscCents > 0)
                    <p><span class="text-gray-500">Remise sur lignes sélectionnées ({{ $discPct }}&nbsp;%) :</span> −{{ number_format($lineDiscCents / 100, 0, ',', ' ') }} FCFA</p>
                @endif
                <p><span class="text-gray-500">Total :</span> <span class="font-semibold">{{ number_format($order->total_cents / 100, 0, ',', ' ') }} FCFA</span></p>
                <p><span class="text-gray-500">Montant versé :</span> {{ number_format($order->advance_payment_cents / 100, 0, ',', ' ') }} FCFA @if ($order->paymentMethodLabel()) <span class="text-gray-500">({{ $order->paymentMethodLabel() }})</span> @endif</p>
                <p><span class="text-gray-500">Paiement :</span>
                    @if ((int) $order->total_cents <= 0)
                        <span class="text-gray-400">—</span>
                    @elseif ($order->isFullyPaid())
                        <span class="font-semibold text-green-700">Payé</span>
                    @else
                        <span class="text-amber-800">Acompte / partiel — reste {{ number_format($order->balanceDueCents() / 100, 0, ',', ' ') }} FCFA</span>
                    @endif
                </p>
                @if ($order->model_notes)
                    <p class="pt-2 border-t text-gray-700"><span class="text-gray-500">Notes modèle :</span> {{ $order->model_notes }}</p>
                @endif
                @if ($order->notes)
                    <p class="pt-1 text-gray-700"><span class="text-gray-500">Notes internes :</span> {{ $order->notes }}</p>
                @endif
            </div>

            @if ($order->images->isNotEmpty())
                <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-4">
                    <h3 class="font-medium text-mansa-black mb-3">Images du modèle</h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($order->images as $img)
                            <a href="{{ $img->url() }}" target="_blank" rel="noopener" class="block">
                                <img src="{{ $img->url() }}" alt="" class="h-28 w-28 object-cover rounded-md border border-gray-200 hover:opacity-90" />
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @unless ($isTailor)
            <div class="rounded-lg border border-gold-200 bg-gold-50/40 p-4 space-y-3 text-sm">
                <h3 class="font-semibold text-mansa-black">Stock atelier</h3>
                @if ($order->inventory_deducted_at)
                    <p class="text-green-900">Stock déduit le <strong>{{ $order->inventory_deducted_at->format('d/m/Y à H:i') }}</strong> (une fois par commande).</p>
                @else
                    <p class="text-gray-700">Réception fournisseur = entrée de stock ; passage en <strong>Livré</strong> ou bouton ci-dessous = sortie (articles simples en quantité, tissu léger : mètres sur la ligne choisie à la création de la commande).</p>
                    <form method="POST" action="{{ route('orders.deduct-inventory', $order) }}" onsubmit="return confirm('Confirmer la déduction de stock pour cette commande ?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-mansa-black uppercase tracking-widest hover:bg-gold-400">
                            Déduire le stock
                        </button>
                    </form>
                @endif
            </div>
            @endunless

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 font-medium text-sm">Articles</div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Modèle</th>
                            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Description</th>
                            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Stock lié</th>
                            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Qté</th>
                            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">PU</th>
                            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Tissu</th>
                            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Remise</th>
                            <th class="px-4 py-2 text-right text-xs text-gray-500 uppercase">Ligne</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->items as $item)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-600">{{ $item->measurementTemplate?->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm">{{ $item->description }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                    @if ($item->inventoryItem)
                                        <span class="font-medium">{{ $item->inventoryItem->name }}</span>
                                        @if ($item->inventory_consumed_meters !== null && $item->inventory_characteristic_key)
                                            @php
                                                $rowLabel = $item->inventory_characteristic_key;
                                                foreach ($item->inventoryItem->characteristicRowsForActualiser() as $row) {
                                                    if (($row['key'] ?? '') === $item->inventory_characteristic_key) {
                                                        $rowLabel = $row['label'] ?? $rowLabel;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            <p class="text-xs text-gray-600 mt-1">{{ $rowLabel }} : {{ rtrim(rtrim(number_format((float) $item->inventory_consumed_meters, 3, ',', ' '), '0'), ',') }} m</p>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm">{{ $item->quantity }}</td>
                                <td class="px-4 py-2 text-sm">{{ number_format($item->unit_price_cents / 100, 0, ',', ' ') }} FCFA</td>
                                <td class="px-4 py-2 text-sm text-gray-600">
                                    @if ($item->client_supplies_fabric)
                                        Client
                                    @else
                                        Atelier
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    @if ($item->effectiveLineDiscountCents() > 0)
                                        −{{ number_format($item->effectiveLineDiscountCents() / 100, 0, ',', ' ') }} FCFA
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-right font-medium">{{ number_format($item->lineNetCents() / 100, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-4 text-center text-gray-500 text-sm">Aucune ligne.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-4">
                <h3 class="font-medium text-mansa-black mb-2">Historique des statuts</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    @foreach ($order->statusHistories->sortBy('created_at') as $h)
                        <li>{{ $h->created_at?->format('d/m/Y H:i') }} — <strong>{{ \App\Models\Order::statusLabelFor($h->status) }}</strong> @if($h->user) ({{ $h->user->name }}) @endif</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
