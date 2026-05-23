<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Models\Listing;
use Inertia\Inertia;

class ListingController extends Controller
{
    /**
     * Display a listing of all listings for admin.
     */
    public function index(): \Inertia\Response
    {
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
    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Listings/Create');
    }

    /**
     * Store a newly created listing.
     */
    public function store(StoreListingRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $images = [];
        $rawImages = $request->all('images')['images'] ?? [];
        foreach ($rawImages as $item) {
            if ($item instanceof \Illuminate\Http\UploadedFile) {
                $path = $item->store('listings', 'public');
                $images[] = '/storage/' . $path;
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
    public function edit(Listing $listing): \Inertia\Response
    {
        return Inertia::render('Admin/Listings/Edit', [
            'listing' => $listing,
        ]);
    }

    /**
     * Update the specified listing.
     */
    public function update(UpdateListingRequest $request, Listing $listing): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        
        $images = [];
        $rawImages = $request->all('images')['images'] ?? [];
        foreach ($rawImages as $item) {
            if ($item instanceof \Illuminate\Http\UploadedFile) {
                $path = $item->store('listings', 'public');
                $images[] = '/storage/' . $path;
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
    public function destroy(Listing $listing): \Illuminate\Http\RedirectResponse
    {
        $listing->delete();

        return redirect()->route('admin.listings.index')
            ->with('success', 'Listing deleted successfully.');
    }

    /**
     * Toggle listing active status.
     */
    public function toggleActive(Listing $listing): \Illuminate\Http\RedirectResponse
    {
        $listing->update(['is_active' => ! $listing->is_active]);

        $status = $listing->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Listing {$status} successfully.");
    }
}
