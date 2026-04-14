@php
    /** @var \App\Models\InventoryItem $item */
    /** @var list<array{key: string, label: string, type: string, value: string}> $schemaRows */
    $stockByNumericLines = $item->hasNumericCharacteristicRows();
@endphp

<div class="space-y-5">
    @if ($schemaRows === [])
        <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Aucun intitulé défini. Commencez par <a href="{{ route('inventory.parameterize', $item) }}" class="font-medium underline">Paramétrer les caractéristiques</a>.
        </div>
    @else
        @foreach ($schemaRows as $row)
            @php
                $fid = 'val_'.$row['key'];
                $oval = old('values.'.$row['key'], $row['value']);
            @endphp
            <div>
                <label for="{{ $fid }}" class="block text-sm font-medium text-gray-700">{{ $row['label'] }} :</label>
                @if ($row['type'] === 'number')
                    <input
                        id="{{ $fid }}"
                        name="values[{{ $row['key'] }}]"
                        type="number"
                        step="any"
                        min="0"
                        value="{{ $oval }}"
                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                    />
                @else
                    <input
                        id="{{ $fid }}"
                        name="values[{{ $row['key'] }}]"
                        type="text"
                        value="{{ $oval }}"
                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                    />
                @endif
            </div>
        @endforeach

        @if ($stockByNumericLines)
            <div class="rounded-lg border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-900">
                <p class="font-medium">Stock total en {{ $item->unit }}</p>
                <p class="mt-1 text-xs text-emerald-800">La quantité totale sera calculée automatiquement : somme de tous les champs <strong>nombre</strong> ci-dessus (types / détails).</p>
            </div>
        @else
            <div>
                <x-input-label for="quantity_on_hand" value="Quantité totale en stock ({{ $item->unit }})" />
                <x-text-input
                    id="quantity_on_hand"
                    name="quantity_on_hand"
                    type="number"
                    step="0.001"
                    min="0"
                    class="block mt-1 w-full"
                    required
                    value="{{ old('quantity_on_hand', $item->quantity_on_hand) }}"
                />
                <x-input-error :messages="$errors->get('quantity_on_hand')" class="mt-1" />
            </div>
        @endif
    @endif
</div>
