<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReportingSnapshot;
use Illuminate\View\View;

class ReportingWebController extends Controller
{
    public function index(): View
    {
        $snapshots = ReportingSnapshot::query()->orderByDesc('period_end')->limit(12)->get();
        $ordersCount = Order::query()->count();

        return view('modules.reporting.index', compact('snapshots', 'ordersCount'));
    }
}
