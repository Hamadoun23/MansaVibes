<div class="space-y-4">
    <div>
        <x-input-label for="name" value="Nom du fournisseur" />
        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $supplier?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="phone" value="Téléphone" />
            <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $supplier?->phone)" />
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $supplier?->email)" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
    </div>
    <div>
        <x-input-label for="address" value="Adresse" />
        <textarea id="address" name="address" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('address', $supplier?->address) }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('notes', $supplier?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
    </div>
</div>
