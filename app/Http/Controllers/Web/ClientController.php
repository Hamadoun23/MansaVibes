<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientMeasurement;
use App\Models\MeasurementFormTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::query()->orderBy('name')->paginate(15);

        return view('modules.clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('modules.clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        Client::query()->create($data);

        return redirect()->route('clients.index')->with('status', __('clients.saved'));
    }

    public function show(Client $client): View
    {
        MeasurementFormTemplate::ensureDefaultsForAuthenticatedTenant();

        $client->load([
            'measurements' => fn ($q) => $q->with('measurementTemplate')->orderByDesc('created_at'),
            'orders' => fn ($q) => $q->with(['images', 'measurementTemplate'])->orderByDesc('created_at')->limit(30),
        ]);

        $templates = MeasurementFormTemplate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $measurementCountByTemplate = $client->measurements
            ->whereNotNull('measurement_form_template_id')
            ->countBy(fn ($m) => (int) $m->measurement_form_template_id)
            ->all();

        return view('modules.clients.show', compact('client', 'templates', 'measurementCountByTemplate'));
    }

    public function storeMeasurement(Request $request, Client $client): RedirectResponse
    {
        $data = $this->validatedMeasurement($request, null);

        $client->measurements()->create($data);

        return redirect()->route('clients.show', $client)->with('status', __('clients.measurement_saved'));
    }

    public function editMeasurement(Client $client, ClientMeasurement $measurement): View
    {
        $this->authorizeMeasurement($client, $measurement);

        $measurement->loadMissing('measurementTemplate');

        return view('modules.clients.measurement-edit', compact('client', 'measurement'));
    }

    public function updateMeasurement(Request $request, Client $client, ClientMeasurement $measurement): RedirectResponse
    {
        $this->authorizeMeasurement($client, $measurement);

        $measurement->loadMissing('measurementTemplate');
        $measurement->update($this->validatedMeasurement($request, $measurement));

        return redirect()->route('clients.show', $client)->with('status', __('clients.measurement_updated'));
    }

    public function destroyMeasurement(Client $client, ClientMeasurement $measurement): RedirectResponse
    {
        $this->authorizeMeasurement($client, $measurement);

        $measurement->delete();

        return redirect()->route('clients.show', $client)->with('status', __('clients.measurement_deleted'));
    }

    public function edit(Client $client): View
    {
        return view('modules.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $client->update($data);

        return redirect()->route('clients.index')->with('status', __('clients.updated'));
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->route('clients.index')->with('status', __('clients.deleted'));
    }

    protected function authorizeMeasurement(Client $client, ClientMeasurement $measurement): void
    {
        abort_unless($measurement->client_id === $client->id, 404);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedMeasurement(Request $request, ?ClientMeasurement $existing): array
    {
        if ($existing !== null && $existing->measurement_form_template_id && $existing->measurementTemplate) {
            return $this->validatedMeasurementForTemplate($request, $existing->measurementTemplate);
        }

        $tid = $request->input('measurement_form_template_id');
        if ($tid !== null && $tid !== '') {
            $template = MeasurementFormTemplate::query()->whereKey((int) $tid)->firstOrFail();
            abort_unless((int) $template->tenant_id === (int) $request->user()->tenant_id, 403);

            return $this->validatedMeasurementForTemplate($request, $template);
        }

        return $this->validatedMeasurementLegacy($request);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedMeasurementForTemplate(Request $request, MeasurementFormTemplate $template): array
    {
        $base = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'measurement_notes' => ['nullable', 'string'],
        ]);

        $values = [];
        foreach ($template->normalizedFields() as $f) {
            $key = $f['key'];
            $rules = $f['type'] === 'number'
                ? ["field_values.$key" => ['nullable', 'numeric', 'min:0', 'max:99999']]
                : ["field_values.$key" => ['nullable', 'string', 'max:2000']];
            $request->validate($rules);
            $raw = $request->input("field_values.$key");
            if ($f['type'] === 'number') {
                $values[$key] = $raw === null || $raw === '' ? null : (float) $raw;
            } else {
                $values[$key] = $raw === null || $raw === '' ? null : (string) $raw;
            }
        }

        $dataFiltered = array_filter(
            $values,
            fn ($v) => $v !== null && $v !== ''
        );

        return [
            'label' => $base['description'],
            'measurement_form_template_id' => $template->id,
            'measurement_notes' => $base['measurement_notes'] ?? null,
            'data' => $dataFiltered !== [] ? $dataFiltered : ['_note' => 'aucune valeur saisie'],
            'custom_measures' => [],
            'poitrine_cm' => null,
            'taille_cm' => null,
            'hanche_cm' => null,
            'longueur_cm' => null,
            'epaule_cm' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedMeasurementLegacy(Request $request): array
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'poitrine_cm' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'taille_cm' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'hanche_cm' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'longueur_cm' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'epaule_cm' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'measurement_notes' => ['nullable', 'string'],
            'custom_rows' => ['nullable', 'array'],
            'custom_rows.*.description' => ['nullable', 'string', 'max:120'],
            'custom_rows.*.value' => ['nullable', 'string', 'max:120'],
            'custom_rows.*.unit' => ['nullable', 'string', 'max:30'],
        ]);

        $rows = collect($validated['custom_rows'] ?? [])
            ->map(fn (array $row) => [
                'label' => trim((string) ($row['description'] ?? '')),
                'value' => trim((string) ($row['value'] ?? '')),
                'unit' => trim((string) ($row['unit'] ?? '')),
            ])
            ->filter(fn (array $row) => $row['label'] !== '' && $row['value'] !== '')
            ->values()
            ->all();

        $legacyData = array_filter([
            'poitrine_cm' => $validated['poitrine_cm'] ?? null,
            'taille_cm' => $validated['taille_cm'] ?? null,
            'hanche_cm' => $validated['hanche_cm'] ?? null,
            'longueur_cm' => $validated['longueur_cm'] ?? null,
            'epaule_cm' => $validated['epaule_cm'] ?? null,
            'custom' => $rows,
        ], fn ($v) => $v !== null && $v !== []);

        return [
            'label' => $validated['description'],
            'measurement_form_template_id' => null,
            'poitrine_cm' => $validated['poitrine_cm'] ?? null,
            'taille_cm' => $validated['taille_cm'] ?? null,
            'hanche_cm' => $validated['hanche_cm'] ?? null,
            'longueur_cm' => $validated['longueur_cm'] ?? null,
            'epaule_cm' => $validated['epaule_cm'] ?? null,
            'measurement_notes' => $validated['measurement_notes'] ?? null,
            'custom_measures' => $rows,
            'data' => $legacyData !== [] ? $legacyData : ['note' => 'mensurations structurées'],
        ];
    }
}
