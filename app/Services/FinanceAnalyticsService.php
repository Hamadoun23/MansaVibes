<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\Client;
use App\Models\Employee;
use App\Models\FixedAsset;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinanceAnalyticsService
{
    public function __construct(
        protected int $tenantId,
        protected Carbon $from,
        protected Carbon $to,
        protected array $tenantSettings = []
    ) {}

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        $marginPct = max(0, min(100, (int) ($this->tenantSettings['finance_margin_percent'] ?? 35)));
        $fixedMonthly = (int) ($this->tenantSettings['finance_fixed_monthly_cents'] ?? 0);
        $riskGrowth = (int) ($this->tenantSettings['finance_risk_growth_percent'] ?? 0);

        $caOrders = $this->caOrdersCents();
        $encaissements = $this->encaissementsCents();
        $decaissements = $this->decaissementsCents();
        $chargesCash = $decaissements;

        $payrollMonthly = $this->payrollMonthlyCents();
        $tresorerieNette = $this->tresorerieNetteCents();
        $creances = $this->creancesCents();
        $avancesClients = $this->avancesClientsCents();
        $immobBrut = $this->immobilisationsBrutCents();

        $resultatCash = $encaissements - $decaissements;
        $resultatApprox = $caOrders - $chargesCash;

        $breakEvenCa = $marginPct > 0
            ? (int) ceil($fixedMonthly / ($marginPct / 100))
            : null;

        $avgCaLast3 = $this->avgMonthlyCaOrdersCents(3);
        $avgChargesLast3 = $this->avgMonthlyDecaissementsCents(3);
        $burn = max(0, $avgChargesLast3 - $this->avgMonthlyEncaissementsCents(3));
        $runwayMonths = $burn > 0 ? (int) floor($tresorerieNette / $burn) : null;

        $projection = $this->projectionMonths(3, $riskGrowth, $avgCaLast3, $avgChargesLast3);

        return [
            'period_from' => $this->from,
            'period_to' => $this->to,
            'ca_orders_cents' => $caOrders,
            'encaissements_cents' => $encaissements,
            'decaissements_cents' => $decaissements,
            'charges_cash_cents' => $chargesCash,
            'resultat_cash_cents' => $resultatCash,
            'resultat_approx_cents' => $resultatApprox,
            'margin_percent_config' => $marginPct,
            'fixed_monthly_cents' => $fixedMonthly,
            'break_even_ca_cents' => $breakEvenCa,
            'payroll_monthly_cents' => $payrollMonthly,
            'tresorerie_nette_cents' => $tresorerieNette,
            'creances_cents' => $creances,
            'avances_clients_cents' => $avancesClients,
            'immobilisations_brut_cents' => $immobBrut,
            'actif_simplifie_cents' => $tresorerieNette + $creances + $immobBrut,
            'passif_avances_cents' => $avancesClients,
            'capitaux_propres_approx_cents' => ($tresorerieNette + $creances + $immobBrut) - $avancesClients,
            'top_modeles' => $this->topModeles(),
            'top_depenses_categories' => $this->topDepensesParCategorie(),
            'journal' => $this->journalLines(),
            'risk_growth_percent' => $riskGrowth,
            'avg_ca_3m_cents' => $avgCaLast3,
            'avg_charges_3m_cents' => $avgChargesLast3,
            'burn_monthly_cents' => $burn,
            'runway_months' => $runwayMonths,
            'projection' => $projection,
        ];
    }

    protected function orderBaseQuery(): Builder
    {
        return Order::query()
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('created_at', [$this->from->copy()->startOfDay(), $this->to->copy()->endOfDay()])
            ->whereIn('status', ['delivered', 'validated', 'done']);
    }

    protected function caOrdersCents(): int
    {
        return (int) $this->orderBaseQuery()->sum('total_cents');
    }

    protected function encaissementsCents(): int
    {
        $pay = (int) Payment::query()
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('paid_at', [$this->from->copy()->startOfDay(), $this->to->copy()->endOfDay()])
            ->sum('amount_cents');

        $cashIn = (int) CashMovement::query()
            ->where('tenant_id', $this->tenantId)
            ->where('direction', 'in')
            ->whereBetween('movement_date', [$this->from->copy()->toDateString(), $this->to->copy()->toDateString()])
            ->sum('amount_cents');

        return $pay + $cashIn;
    }

    protected function decaissementsCents(): int
    {
        return (int) CashMovement::query()
            ->where('tenant_id', $this->tenantId)
            ->where('direction', 'out')
            ->whereBetween('movement_date', [$this->from->copy()->toDateString(), $this->to->copy()->toDateString()])
            ->sum('amount_cents');
    }

    protected function tresorerieNetteCents(): int
    {
        $in = (int) CashMovement::query()
            ->where('tenant_id', $this->tenantId)
            ->where('direction', 'in')
            ->sum('amount_cents');

        $out = (int) CashMovement::query()
            ->where('tenant_id', $this->tenantId)
            ->where('direction', 'out')
            ->sum('amount_cents');

        return $in - $out;
    }

    protected function creancesCents(): int
    {
        return (int) Client::query()
            ->where('tenant_id', $this->tenantId)
            ->where('balance_cents', '>', 0)
            ->sum('balance_cents');
    }

    protected function avancesClientsCents(): int
    {
        return (int) abs(Client::query()
            ->where('tenant_id', $this->tenantId)
            ->where('balance_cents', '<', 0)
            ->sum('balance_cents'));
    }

    protected function immobilisationsBrutCents(): int
    {
        return (int) FixedAsset::query()
            ->where('tenant_id', $this->tenantId)
            ->sum('amount_cents');
    }

    protected function payrollMonthlyCents(): int
    {
        return (int) Employee::query()
            ->where('tenant_id', $this->tenantId)
            ->sum('monthly_salary_cents');
    }

    /** @return Collection<int, object{model_key: string, revenue_cents: int, cnt: int}> */
    protected function topModeles(): Collection
    {
        $rows = Order::query()
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('created_at', [$this->from->copy()->startOfDay(), $this->to->copy()->endOfDay()])
            ->whereIn('status', ['delivered', 'validated', 'done', 'in_progress'])
            ->select([
                'model_name',
                'measurement_form_template_id',
                DB::raw('SUM(total_cents) as revenue_cents'),
                DB::raw('COUNT(*) as cnt'),
            ])
            ->groupBy('model_name', 'measurement_form_template_id')
            ->orderByDesc('revenue_cents')
            ->limit(12)
            ->get();

        return $rows->map(function ($row) {
            $name = $row->model_name;
            $label = ($name !== null && $name !== '' && trim((string) $name) !== '')
                ? trim((string) $name)
                : 'Modèle #'.((int) ($row->measurement_form_template_id ?? 0));

            return (object) [
                'model_key' => $label,
                'revenue_cents' => (int) $row->revenue_cents,
                'cnt' => (int) $row->cnt,
            ];
        })->values();
    }

    /** @return Collection<int, object{category_id: int|null, name: string, total_cents: int}> */
    protected function topDepensesParCategorie(): Collection
    {
        $rows = CashMovement::query()
            ->where('tenant_id', $this->tenantId)
            ->where('direction', 'out')
            ->whereBetween('movement_date', [$this->from->copy()->toDateString(), $this->to->copy()->toDateString()])
            ->select([
                'finance_category_id',
                DB::raw('SUM(amount_cents) as total_cents'),
            ])
            ->groupBy('finance_category_id')
            ->orderByDesc('total_cents')
            ->limit(15)
            ->get();

        $catIds = $rows->pluck('finance_category_id')->filter()->unique()->all();
        $cats = \App\Models\FinanceCategory::query()->whereIn('id', $catIds)->pluck('name', 'id');

        return $rows->map(function ($row) use ($cats) {
            $name = $row->finance_category_id
                ? ($cats[$row->finance_category_id] ?? '—')
                : 'Sans catégorie';

            return (object) [
                'category_id' => $row->finance_category_id,
                'name' => $name,
                'total_cents' => (int) $row->total_cents,
            ];
        });
    }

    /** @return Collection<int, object{type: string, date: string, label: string, amount_cents: int, direction: string}> */
    protected function journalLines(): Collection
    {
        $movements = CashMovement::query()
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('movement_date', [$this->from->copy()->toDateString(), $this->to->copy()->toDateString()])
            ->with('category')
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (CashMovement $m) => (object) [
                'type' => 'caisse',
                'date' => $m->movement_date->format('Y-m-d'),
                'label' => $m->label.($m->category ? ' · '.$m->category->name : ''),
                'amount_cents' => $m->amount_cents,
                'direction' => $m->direction,
            ]);

        $payments = Payment::query()
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('paid_at', [$this->from->copy()->startOfDay(), $this->to->copy()->endOfDay()])
            ->with('invoice')
            ->orderByDesc('paid_at')
            ->get()
            ->map(fn (Payment $p) => (object) [
                'type' => 'paiement_facture',
                'date' => $p->paid_at?->format('Y-m-d') ?? '',
                'label' => 'Paiement '.($p->invoice?->number ?? '#'.$p->invoice_id),
                'amount_cents' => $p->amount_cents,
                'direction' => 'in',
            ]);

        return $movements->concat($payments)->sortByDesc('date')->values();
    }

    protected function avgMonthlyCaOrdersCents(int $months): int
    {
        $start = $this->to->copy()->subMonths($months)->startOfMonth();

        $total = (int) Order::query()
            ->where('tenant_id', $this->tenantId)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $this->to->copy()->endOfDay())
            ->whereIn('status', ['delivered', 'validated', 'done'])
            ->sum('total_cents');

        return (int) round($total / max(1, $months));
    }

    protected function avgMonthlyDecaissementsCents(int $months): int
    {
        $start = $this->to->copy()->subMonths($months)->startOfMonth();

        $total = (int) CashMovement::query()
            ->where('tenant_id', $this->tenantId)
            ->where('direction', 'out')
            ->where('movement_date', '>=', $start->toDateString())
            ->where('movement_date', '<=', $this->to->copy()->toDateString())
            ->sum('amount_cents');

        return (int) round($total / max(1, $months));
    }

    protected function avgMonthlyEncaissementsCents(int $months): int
    {
        $start = $this->to->copy()->subMonths($months)->startOfMonth();

        $pay = (int) Payment::query()
            ->where('tenant_id', $this->tenantId)
            ->where('paid_at', '>=', $start)
            ->where('paid_at', '<=', $this->to->copy()->endOfDay())
            ->sum('amount_cents');

        $cash = (int) CashMovement::query()
            ->where('tenant_id', $this->tenantId)
            ->where('direction', 'in')
            ->where('movement_date', '>=', $start->toDateString())
            ->where('movement_date', '<=', $this->to->copy()->toDateString())
            ->sum('amount_cents');

        $total = $pay + $cash;

        return (int) round($total / max(1, $months));
    }

    /**
     * @return list<array{month: string, ca_projete_cents: int, charges_projetees_cents: int, solde_cumule_cents: int}>
     */
    protected function projectionMonths(int $horizon, int $growthPercent, int $baseCaMonthly, int $baseChargesMonthly): array
    {
        $out = [];
        $cumul = 0;
        $g = 1 + ($growthPercent / 100);

        for ($i = 1; $i <= $horizon; $i++) {
            $ca = (int) round($baseCaMonthly * pow($g, $i - 1));
            $ch = $baseChargesMonthly;
            $cumul += $ca - $ch;
            $out[] = [
                'month' => $this->to->copy()->addMonths($i)->translatedFormat('M Y'),
                'ca_projete_cents' => $ca,
                'charges_projetees_cents' => $ch,
                'solde_cumule_cents' => $cumul,
            ];
        }

        return $out;
    }
}
