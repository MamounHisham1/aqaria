<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    /**
     * Display the admin lead inbox with pipeline summary.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Lead::class);

        $status = $request->input('status');

        $leads = Lead::query()
            ->with(['listing' => function ($q) {
                $q->select('id', 'title', 'city', 'district', 'price', 'listing_type');
            }])
            ->when($status, fn ($q) => $q->status($status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Lead::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return Inertia::render('Admin/Leads/Index', [
            'leads' => $leads,
            'statuses' => Lead::STATUSES,
            'statusCounts' => $statusCounts,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Display a single lead with its listing context.
     */
    public function show(Lead $lead): Response
    {
        Gate::authorize('view', $lead);

        $lead->load('listing');

        return Inertia::render('Admin/Leads/Show', [
            'lead' => $lead,
            'statuses' => Lead::STATUSES,
        ]);
    }

    /**
     * Update a lead's status and/or admin notes.
     */
    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        Gate::authorize('update', $lead);

        $data = $request->validated();

        if (isset($data['status'])) {
            $data = $this->applyStatusTimestamp($lead, $data['status'], $data);
        }

        $lead->update($data);

        return redirect()
            ->route('admin.leads.show', $lead)
            ->with('success', 'Lead updated.');
    }

    /**
     * Stamp the relevant transition timestamps when the status changes.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyStatusTimestamp(Lead $lead, string $status, array $data): array
    {
        if ($status === 'contacted' && $lead->contacted_at === null) {
            $data['contacted_at'] = now();
        }

        if ($status === 'closed' && $lead->closed_at === null) {
            $data['closed_at'] = now();
        }

        return $data;
    }
}
