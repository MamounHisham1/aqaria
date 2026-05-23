<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingView;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ListingController extends Controller
{
    /**
     * Display a searchable, filterable list of listings.
     */
    public function index(Request $request): \Inertia\Response
    {
        $query = Listing::query()->active()->withCount('views')->withCount('clicks');

        // Search
        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        // Filters
        if ($request->filled('city')) {
            $query->inCity($request->input('city'));
        }

        if ($request->filled('listing_type')) {
            $query->byListingType($request->input('listing_type'));
        }

        if ($request->filled('property_type')) {
            $query->byPropertyType($request->input('property_type'));
        }

        if ($request->filled('min_price')) {
            $query->priceBetween($request->input('min_price'), $request->input('max_price'));
        } elseif ($request->filled('max_price')) {
            $query->priceBetween(null, $request->input('max_price'));
        }

        if ($request->filled('min_area')) {
            $query->areaBetween($request->input('min_area'), $request->input('max_area'));
        } elseif ($request->filled('max_area')) {
            $query->areaBetween(null, $request->input('max_area'));
        }

        if ($request->filled('bedrooms')) {
            $query->withBedrooms($request->input('bedrooms'));
        }

        if ($request->filled('bathrooms')) {
            $query->withBathrooms($request->input('bathrooms'));
        }

        // Sort
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'area_asc' => $query->orderBy('area_sqm', 'asc'),
            'area_desc' => $query->orderBy('area_sqm', 'desc'),
            default => $query->latest(),
        };

        $listings = $query->paginate(12)->withQueryString();

        // Get filter options
        $cities = Listing::distinct()->pluck('city')->sort()->values();

        return Inertia::render('Listings/Index', [
            'listings' => $listings,
            'filters' => $request->only([
                'q',
                'city',
                'listing_type',
                'property_type',
                'min_price',
                'max_price',
                'min_area',
                'max_area',
                'bedrooms',
                'bathrooms',
                'sort',
            ]),
            'filterOptions' => [
                'cities' => $cities,
                'propertyTypes' => ['apartment', 'villa', 'townhouse', 'commercial'],
                'listingTypes' => ['sale', 'rent'],
            ],
        ]);
    }

    /**
     * Display a single listing with full details and track view.
     */
    public function show(Request $request, Listing $listing): \Inertia\Response
    {
        if (! $listing->is_active) {
            abort(404);
        }

        $visitorId = $request->cookie('visitor_id');

        // Track view (deduplicate: same visitor + same listing within 24h)
        if ($visitorId) {
            $recentView = ListingView::query()
                ->forListing($listing->id)
                ->byVisitor($visitorId)
                ->where('viewed_at', '>=', now()->subDay())
                ->exists();

            if (! $recentView) {
                ListingView::create([
                    'listing_id' => $listing->id,
                    'visitor_id' => $visitorId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'viewed_at' => now(),
                ]);
            }
        }

        // Load relationships
        $listing->loadCount('views');
        $listing->loadCount('clicks');

        // Get related listings from same city
        $relatedListings = Listing::query()
            ->active()
            ->where('id', '!=', $listing->id)
            ->where('city', $listing->city)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return Inertia::render('Listings/Show', [
            'listing' => $listing,
            'relatedListings' => $relatedListings,
        ]);
    }

    /**
     * Record a click event on a listing.
     */
    public function recordClick(Request $request, Listing $listing): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'click_type' => 'required|in:phone,whatsapp,detail',
        ]);

        $visitorId = $request->cookie('visitor_id');

        if ($visitorId) {
            \App\Models\ListingClick::create([
                'listing_id' => $listing->id,
                'visitor_id' => $visitorId,
                'click_type' => $request->input('click_type'),
                'clicked_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
