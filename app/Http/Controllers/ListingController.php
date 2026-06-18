<?php

namespace App\Http\Controllers;

use App\Jobs\RecordListingClick;
use App\Jobs\RecordListingView;
use App\Models\Listing;
use App\Support\ListingFilters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    /**
     * Display a searchable, filterable list of listings.
     */
    public function index(Request $request): Response
    {
        $query = Listing::query()->active()->withCount('views')->withCount('clicks');

        // Apply the canonical set of listing filters (single source of truth).
        ListingFilters::apply($query, $request->only(ListingFilters::keys()));

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
            'filters' => $request->only([...ListingFilters::keys(), 'sort']),
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
    public function show(Request $request, Listing $listing): Response
    {
        if (! $listing->is_active) {
            abort(404);
        }

        $visitorId = $request->cookie('visitor_id');

        // Track view asynchronously (dedup happens inside the job).
        if ($visitorId) {
            RecordListingView::dispatch(
                $listing->id,
                $visitorId,
                $request->ip(),
                $request->userAgent(),
            );
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
            'seo' => [
                'title' => $listing->title.' — '.$listing->formatted_price,
                'description' => $listing->description,
                'image' => $listing->primary_image,
                'url' => route('listings.show', $listing),
                'schema' => $this->buildListingSchema($listing),
            ],
        ]);
    }

    /**
     * Build Schema.org Product/SingleFamilyResidence structured data.
     *
     * @return array<string, mixed>
     */
    private function buildListingSchema(Listing $listing): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $listing->title,
            'description' => $listing->description,
            'url' => route('listings.show', $listing),
        ];

        if ($listing->primary_image) {
            $schema['image'] = $listing->primary_image;
        }

        $offers = [
            '@type' => 'Offer',
            'price' => (float) $listing->price,
            'priceCurrency' => 'EGP',
            'availability' => $listing->listing_type === 'sale'
                ? 'https://schema.org/InStock'
                : 'https://schema.org/InStock',
        ];

        $schema['offers'] = $offers;

        $address = [
            '@type' => 'PostalAddress',
            'addressLocality' => $listing->city,
            'addressRegion' => $listing->district,
            'streetAddress' => $listing->address,
        ];

        $schema['address'] = $address;

        if ($listing->latitude && $listing->longitude) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $listing->latitude,
                'longitude' => (float) $listing->longitude,
            ];
        }

        return $schema;
    }

    /**
     * Record a click event on a listing.
     */
    public function recordClick(Request $request, Listing $listing): JsonResponse
    {
        $request->validate([
            'click_type' => 'required|in:phone,whatsapp,detail',
        ]);

        $visitorId = $request->cookie('visitor_id');

        if ($visitorId) {
            RecordListingClick::dispatch(
                $listing->id,
                $visitorId,
                $request->input('click_type'),
            );
        }

        return response()->json(['success' => true]);
    }
}
