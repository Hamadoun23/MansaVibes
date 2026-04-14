<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\FinanceAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->string('from')->toString())->startOfDay()
            : now()->copy()->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->string('to')->toString())->endOfDay()
            : now()->copy()->endOfMonth();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $tenant = $request->user()->tenant;
        $settings = $tenant->settings ?? [];

        $analytics = new FinanceAnalyticsService(
            (int) $tenant->id,
            $from,
            $to,
            is_array($settings) ? $settings : []
        );

        $report = $analytics->snapshot();

        return view('modules.finance.index', [
            'report' => $report,
            'from' => $from,
            'to' => $to,
            'settings' => is_array($settings) ? $settings : [],
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'finance_margin_percent' => ['required', 'integer', 'min:0', 'max:95'],
            'finance_fixed_monthly_fcfa' => ['required', 'numeric', 'min:0'],
            'finance_risk_growth_percent' => ['required', 'integer', 'min:-50', 'max:100'],
        ]);

        /** @var Tenant $tenant */
        $tenant = $request->user()->tenant;
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['finance_margin_percent'] = (int) $data['finance_margin_percent'];
        $settings['finance_fixed_monthly_cents'] = (int) round((float) $data['finance_fixed_monthly_fcfa'] * 100);
        $settings['finance_risk_growth_percent'] = (int) $data['finance_risk_growth_percent'];
        $tenant->update(['settings' => $settings]);

        return redirect()->route('finance.index', $request->only(['from', 'to']))->with('status', 'Paramètres financiers enregistrés.');
    }
}
