<?php

namespace App\Http\Controllers;

use App\Models\SavedSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SavedSearchController extends Controller
{
    /**
     * Store the current search filters as a saved search for the user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'notify' => ['boolean'],
            // Capture only the filter keys that the listings index understands.
            'filters.q' => ['nullable', 'string', 'max:200'],
            'filters.city' => ['nullable', 'string', 'max:100'],
            'filters.listing_type' => ['nullable', Rule::in(['sale', 'rent'])],
            'filters.property_type' => ['nullable', Rule::in(['apartment', 'villa', 'townhouse', 'commercial'])],
            'filters.min_price' => ['nullable', 'numeric', 'min:0'],
            'filters.max_price' => ['nullable', 'numeric', 'min:0'],
            'filters.min_area' => ['nullable', 'integer', 'min:0'],
            'filters.max_area' => ['nullable', 'integer', 'min:0'],
            'filters.bedrooms' => ['nullable', 'integer', 'min:0'],
            'filters.bathrooms' => ['nullable', 'integer', 'min:0'],
        ]);

        $request->user()->savedSearches()->create([
            'name' => $validated['name'],
            'filters' => $validated['filters'] ?? [],
            'notify' => $validated['notify'] ?? false,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Search saved.');
    }

    public function destroy(Request $request, SavedSearch $savedSearch): RedirectResponse
    {
        if ($savedSearch->user_id !== $request->user()->id) {
            abort(403);
        }

        $savedSearch->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Search removed.');
    }
}
