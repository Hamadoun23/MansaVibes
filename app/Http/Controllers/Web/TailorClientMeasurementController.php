<?php

namespace App\Http\Controllers\Web;

use App\Models\Client;
use App\Models\MeasurementFormTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TailorClientMeasurementController extends ClientController
{
    public function create(): View
    {
        abort_unless(request()->user()?->role === 'tailleur', 403);

        MeasurementFormTemplate::ensureDefaultsForAuthenticatedTenant();

        $templates = MeasurementFormTemplate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('modules.tailor.client-measurement-create', compact('templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'tailleur', 403);

        $tenantId = (int) $request->user()->tenant_id;

        $clientFields = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'measurement_form_template_id' => [
                'required',
                Rule::exists('measurement_form_templates', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
        ]);

        $templateId = (int) $clientFields['measurement_form_template_id'];
        unset($clientFields['measurement_form_template_id']);

        $client = DB::transaction(function () use ($request, $clientFields, $templateId) {
            $client = Client::query()->create($clientFields);
            $request->merge(['measurement_form_template_id' => $templateId]);
            $payload = $this->validatedMeasurement($request, null);
            $client->measurements()->create($payload);

            return $client;
        });

        return redirect()
            ->route('clients.show', $client)
            ->with('status', __('tailor.client_and_measurement_saved'));
    }
}
