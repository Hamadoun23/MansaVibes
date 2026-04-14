<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TailorWorkspaceController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        if ($user->role !== 'tailleur') {
            abort(403);
        }

        $employee = $user->employee;
        if ($employee === null) {
            abort(403, 'Compte tailleur sans fiche employé liée.');
        }

        $orders = Order::query()
            ->with(['client', 'assignee'])
            ->where('assigned_to', $employee->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('modules.tailor.workspace', compact('orders', 'employee'));
    }
}
