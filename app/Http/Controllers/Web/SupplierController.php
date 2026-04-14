<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::query()
            ->orderBy('name')
            ->paginate(20);

        return view('modules.suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('modules.suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Supplier::query()->create($data);

        return redirect()->route('suppliers.index')->with('status', 'Fournisseur enregistré.');
    }

    public function show(Supplier $supplier): View
    {
        $movements = StockMovement::query()
            ->where('supplier_id', $supplier->id)
            ->with('item')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('modules.suppliers.show', compact('supplier', 'movements'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('modules.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $data = $this->validated($request);
        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('status', 'Fournisseur mis à jour.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('status', 'Fournisseur supprimé.');
    }

    /**
     * @return array{name: string, phone: ?string, email: ?string, address: ?string, notes: ?string}
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        foreach (['phone', 'email', 'address', 'notes'] as $k) {
            if (isset($data[$k]) && (string) $data[$k] === '') {
                $data[$k] = null;
            }
        }

        return $data;
    }
}
