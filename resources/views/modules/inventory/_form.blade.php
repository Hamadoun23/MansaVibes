@include('modules.inventory._article_settings_fields', ['item' => $item])

@if ($item === null)
    <p class="text-sm text-gray-600 rounded-md border border-gold-200 bg-gold-50/40 px-3 py-2">
        Après enregistrement : <strong>Paramétrer</strong> les intitulés des champs, puis <strong>Actualiser</strong> pour saisir les quantités en stock.
    </p>
@endif
