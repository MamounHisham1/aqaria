<?php

namespace App\Http\Controllers;

use App\Models\SavedSearch;
use App\Support\ListingFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedSearchController extends Controller
{
    /**
     * Store the current search filters as a saved search for the user.
     */
    public function store(Request $request): RedirectResponse
    {
        $filterRules = collect(ListingFilters::rules())
            ->mapWithKeys(fn ($rules, $key) => ["filters.{$key}" => $rules])
            ->all();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'notify' => ['boolean'],
            ...$filterRules,
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
