<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffWebController extends Controller
{
    public function index(): View
    {
        $employees = Employee::query()->orderBy('name')->paginate(20);

        return view('modules.staff.index', compact('employees'));
    }

    public function updateSalary(Request $request, Employee $employee): RedirectResponse
    {
        $data = $request->validate([
            'monthly_salary_fcfa' => ['nullable', 'numeric', 'min:0'],
        ]);

        $employee->update([
            'monthly_salary_cents' => (int) round((float) ($data['monthly_salary_fcfa'] ?? 0) * 100),
        ]);

        return redirect()->route('staff.index')->with('status', 'Salaire mensuel enregistré pour '.$employee->name.'.');
    }
}
