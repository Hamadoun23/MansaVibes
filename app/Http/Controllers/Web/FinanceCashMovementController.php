<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CashMovement;
use App\Models\FinanceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinanceCashMovementController extends Controller
{
    public function index(): View
    {
        $movements = CashMovement::query()
            ->with('category')
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(40);

        return view('modules.finance.cash-movements.index', compact('movements'));
    }

    public function create(): View
    {
        $categories = FinanceCategory::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('modules.finance.cash-movements.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = (int) $request->user()->tenant_id;
        $data = $request->validate([
            'direction' => ['required', Rule::in(['in', 'out'])],
            'amount_fcfa' => ['required', 'numeric', 'min:0.01'],
            'label' => ['required', 'string', 'max:255'],
            'movement_date' => ['required', 'date'],
            'finance_category_id' => [
                'nullable',
                Rule::exists('finance_categories', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        CashMovement::query()->create([
            'direction' => $data['direction'],
            'amount_cents' => (int) round((float) $data['amount_fcfa'] * 100),
            'label' => $data['label'],
            'movement_date' => $data['movement_date'],
            'finance_category_id' => $data['finance_category_id'] ?: null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('finance.cash-movements.index')->with('status', 'Mouvement enregistré.');
    }

    public function edit(CashMovement $cashMovement): View
    {
        $categories = FinanceCategory::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('modules.finance.cash-movements.edit', compact('cashMovement', 'categories'));
    }

    public function update(Request $request, CashMovement $cashMovement): RedirectResponse
    {
        $tenantId = (int) $request->user()->tenant_id;
        $data = $request->validate([
            'direction' => ['required', Rule::in(['in', 'out'])],
            'amount_fcfa' => ['required', 'numeric', 'min:0.01'],
            'label' => ['required', 'string', 'max:255'],
            'movement_date' => ['required', 'date'],
            'finance_category_id' => [
                'nullable',
                Rule::exists('finance_categories', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $cashMovement->update([
            'direction' => $data['direction'],
            'amount_cents' => (int) round((float) $data['amount_fcfa'] * 100),
            'label' => $data['label'],
            'movement_date' => $data['movement_date'],
            'finance_category_id' => $data['finance_category_id'] ?: null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('finance.cash-movements.index')->with('status', 'Mouvement mis à jour.');
    }

    public function destroy(CashMovement $cashMovement): RedirectResponse
    {
        $cashMovement->delete();

        return redirect()->route('finance.cash-movements.index')->with('status', 'Mouvement supprimé.');
    }
}
