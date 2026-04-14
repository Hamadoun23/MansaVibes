@php
    /** @var \App\Models\InventoryItem|null $item */
@endphp
<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="Nom de l’article" />
        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $item?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="unit" value="Unité" />
        <x-text-input id="unit" name="unit" class="block mt-1 w-full" :value="old('unit', $item?->unit ?? 'm')" required />
        <x-input-error :messages="$errors->get('unit')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="reorder_level" value="Seuil d’alerte (réappro.)" />
        <x-text-input id="reorder_level" name="reorder_level" type="number" step="0.001" min="0" class="block mt-1 w-full" :value="old('reorder_level', $item?->reorder_level ?? '0')" required />
        <x-input-error :messages="$errors->get('reorder_level')" class="mt-1" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes internes (optionnel)" />
        <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('notes', $item?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
    </div>
</div>
