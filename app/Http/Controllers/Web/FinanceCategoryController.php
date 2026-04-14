<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FinanceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinanceCategoryController extends Controller
{
    public function index(): View
    {
        $categories = FinanceCategory::query()->orderBy('sort_order')->orderBy('name')->paginate(30);

        return view('modules.finance.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('modules.finance.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(['expense', 'income'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        FinanceCategory::query()->create([
            'name' => $data['name'],
            'type' => $data['type'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return redirect()->route('finance.categories.index')->with('status', 'Catégorie enregistrée.');
    }

    public function edit(FinanceCategory $category): View
    {
        return view('modules.finance.categories.edit', compact('category'));
    }

    public function update(Request $request, FinanceCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(['expense', 'income'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $category->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return redirect()->route('finance.categories.index')->with('status', 'Catégorie mise à jour.');
    }

    public function destroy(FinanceCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('finance.categories.index')->with('status', 'Catégorie supprimée.');
    }
}
