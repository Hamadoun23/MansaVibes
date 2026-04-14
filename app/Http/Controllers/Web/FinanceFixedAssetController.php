<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceFixedAssetController extends Controller
{
    public function index(): View
    {
        $assets = FixedAsset::query()->orderByDesc('acquisition_date')->paginate(25);

        return view('modules.finance.fixed-assets.index', compact('assets'));
    }

    public function create(): View
    {
        return view('modules.finance.fixed-assets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'acquisition_date' => ['required', 'date'],
            'amount_fcfa' => ['required', 'numeric', 'min:0.01'],
            'useful_life_months' => ['nullable', 'integer', 'min:1', 'max:600'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        FixedAsset::query()->create([
            'name' => $data['name'],
            'acquisition_date' => $data['acquisition_date'],
            'amount_cents' => (int) round((float) $data['amount_fcfa'] * 100),
            'useful_life_months' => $data['useful_life_months'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('finance.fixed-assets.index')->with('status', 'Immobilisation enregistrée.');
    }

    public function edit(FixedAsset $fixedAsset): View
    {
        return view('modules.finance.fixed-assets.edit', compact('fixedAsset'));
    }

    public function update(Request $request, FixedAsset $fixedAsset): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'acquisition_date' => ['required', 'date'],
            'amount_fcfa' => ['required', 'numeric', 'min:0.01'],
            'useful_life_months' => ['nullable', 'integer', 'min:1', 'max:600'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $fixedAsset->update([
            'name' => $data['name'],
            'acquisition_date' => $data['acquisition_date'],
            'amount_cents' => (int) round((float) $data['amount_fcfa'] * 100),
            'useful_life_months' => $data['useful_life_months'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('finance.fixed-assets.index')->with('status', 'Immobilisation mise à jour.');
    }

    public function destroy(FixedAsset $fixedAsset): RedirectResponse
    {
        $fixedAsset->delete();

        return redirect()->route('finance.fixed-assets.index')->with('status', 'Immobilisation supprimée.');
    }
}
