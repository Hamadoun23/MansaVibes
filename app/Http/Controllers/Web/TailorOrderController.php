<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TailorOrderController extends Controller
{
    public function validateOrder(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'tailleur') {
            abort(403);
        }

        $employee = $user->employee;
        if ($employee === null || (int) $order->assigned_to !== (int) $employee->id) {
            abort(403, __('tailor.cannot_validate_assigned'));
        }

        if (in_array($order->status, ['validated', 'delivered'], true)) {
            return redirect()
                ->route('tailor.workspace')
                ->with('status', __('tailor.order_already_finalized'));
        }

        DB::transaction(function () use ($request, $order): void {
            $previous = $order->status;
            $order->update(['status' => 'validated']);
            if ($previous !== 'validated') {
                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'status' => 'validated',
                    'user_id' => $request->user()->id,
                ]);
            }
        });

        return redirect()
            ->route('tailor.workspace')
            ->with('status', __('tailor.order_marked_validated'));
    }
}
