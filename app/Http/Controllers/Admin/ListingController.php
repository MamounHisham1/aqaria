<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    /**
     * Display a listing of all listings for admin.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', Listing::class);

        $listings = Listing::query()
            ->withCount('views')
            ->withCount('clicks')
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Listings/Index', [
            'listings' => $listings,
        ]);
    }

    /**
     * Show the form for creating a new listing.
     */
    public function create(): Response
    {
        Gate::authorize('create', Listing::class);

        return Inertia::render('Admin/Listings/Create');
    }

    /**
     * Store a newly created listing.
     */
    public function store(StoreListingRequest $request): RedirectResponse
    {
        Gate::authorize('create', Listing::class);

        $data = $request->validated();
        $images = [];
        $rawImages = $request->all('images')['images'] ?? [];
        foreach ($rawImages as $item) {
            if ($item instanceof UploadedFile) {
                $path = $item->store('listings', 'public');
                $images[] = '/storage/'.$path;
            } elseif (is_string($item)) {
                $images[] = $item;
            }
        }
        $data['images'] = $images;
        $data['amenities'] = $request->input('amenities', []);

        Listing::create($data);

        return redirect()->route('admin.listings.index')
            ->with('success', 'Listing created successfully.');
    }

    /**
     * Show the form for editing a listing.
     */
    public function edit(Listing $listing): Response
    {
        Gate::authorize('update', $listing);

        return Inertia::render('Admin/Listings/Edit', [
            'listing' => $listing,
        ]);
    }

    /**
     * Update the specified listing.
     */
    public function update(UpdateListingRequest $request, Listing $listing): RedirectResponse
    {
        Gate::authorize('update', $listing);

        $data = $request->validated();

        $images = [];
        $rawImages = $request->all('images')['images'] ?? [];
        foreach ($rawImages as $item) {
            if ($item instanceof UploadedFile) {
                $path = $item->store('listings', 'public');
                $images[] = '/storage/'.$path;
            } elseif (is_string($item)) {
                $images[] = $item;
            }
        }
        $data['images'] = $images;
        $data['amenities'] = $request->input('amenities', []);

        $listing->update($data);

        return redirect()->route('admin.listings.index')
            ->with('success', 'Listing updated successfully.');
    }

    /**
     * Remove the specified listing.
     */
    public function destroy(Listing $listing): RedirectResponse
    {
        Gate::authorize('delete', $listing);

        $listing->delete();

        return redirect()->route('admin.listings.index')
            ->with('success', 'Listing deleted successfully.');
    }

    /**
     * Toggle listing active status.
     */
    public function toggleActive(Listing $listing): RedirectResponse
    {
        Gate::authorize('toggleActive', $listing);

        $listing->update(['is_active' => ! $listing->is_active]);

        $status = $listing->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Listing {$status} successfully.");
    }
}
