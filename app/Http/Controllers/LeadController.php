<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Jobs\NotifyAdminOfNewLead;
use App\Models\Lead;
use App\Models\Listing;
use Illuminate\Http\RedirectResponse;

class LeadController extends Controller
{
    /**
     * Store a new lead (inquiry) for a listing and notify the admin.
     */
    public function store(StoreLeadRequest $request, Listing $listing): RedirectResponse
    {
        if (! $listing->is_active) {
            abort(404);
        }

        $lead = Lead::create([
            ...$request->validated(),
            'listing_id' => $listing->id,
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        NotifyAdminOfNewLead::dispatch($lead);

        return redirect()
            ->route('listings.show', $listing)
            ->with('success', 'Your inquiry has been sent. We will contact you soon.');
    }
}
