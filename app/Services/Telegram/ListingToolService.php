<?php

namespace App\Services\Telegram;

use App\Models\Listing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class ListingToolService
{
    public function searchListings(array $params): array
    {
        $query = Listing::query();

        if (! empty($params['city'])) {
            $query->inCity($params['city']);
        }

        if (! empty($params['listing_type'])) {
            $query->byListingType($params['listing_type']);
        }

        if (! empty($params['property_type'])) {
            $query->byPropertyType($params['property_type']);
        }

        if (! empty($params['search_term'])) {
            $query->search($params['search_term']);
        }

        if (isset($params['min_price']) || isset($params['max_price'])) {
            $query->priceBetween($params['min_price'] ?? null, $params['max_price'] ?? null);
        }

        if (isset($params['min_bedrooms'])) {
            $query->withBedrooms((int) $params['min_bedrooms']);
        }

        if (isset($params['is_active'])) {
            $params['is_active'] ? $query->active() : $query->where('is_active', false);
        } else {
            $query->active();
        }

        $listings = $query->latest()->limit(10)->get();

        return [
            'count' => $listings->count(),
            'listings' => $listings->map(fn(Listing $l) => $this->formatListing($l))->toArray(),
        ];
    }

    public function createListing(array $params): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'area_sqm' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'property_type' => 'required|string|in:apartment,villa,townhouse,commercial',
            'listing_type' => 'required|string|in:sale,rent',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_phone' => 'required|string|max:20',
        ];

        $validator = Validator::make($params, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'error' => 'Validation failed: ' . $validator->errors()->first(),
            ];
        }

        $listing = Listing::create($validator->validated());

        return [
            'success' => true,
            'listing' => $this->formatListing($listing),
        ];
    }

    public function updateListing(array $params): array
    {
        if (empty($params['id'])) {
            return ['success' => false, 'error' => 'Listing ID is required.'];
        }

        try {
            $listing = Listing::findOrFail((int) $params['id']);
        } catch (ModelNotFoundException) {
            return ['success' => false, 'error' => "Listing #{$params['id']} not found."];
        }

        $rules = [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'area_sqm' => 'sometimes|integer|min:1',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'property_type' => 'sometimes|string|in:apartment,villa,townhouse,commercial',
            'listing_type' => 'sometimes|string|in:sale,rent',
            'city' => 'sometimes|string|max:255',
            'district' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
        ];

        $validator = Validator::make($params, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'error' => 'Validation failed: ' . $validator->errors()->first(),
            ];
        }

        $listing->update($validator->validated());

        return [
            'success' => true,
            'listing' => $this->formatListing($listing->fresh()),
        ];
    }

    public function deleteListing(int $id): array
    {
        try {
            $listing = Listing::findOrFail($id);
        } catch (ModelNotFoundException) {
            return ['success' => false, 'error' => "Listing #{$id} not found."];
        }

        $title = $listing->title;
        $listing->delete();

        return ['success' => true, 'message' => "Listing #{$id} '{$title}' deleted successfully."];
    }

    public function getListingById(int $id): array
    {
        try {
            $listing = Listing::findOrFail($id);
        } catch (ModelNotFoundException) {
            return ['success' => false, 'error' => "Listing #{$id} not found."];
        }

        return ['success' => true, 'listing' => $this->formatListing($listing)];
    }

    private function formatListing(Listing $listing): array
    {
        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'price' => $listing->formatted_price,
            'city' => $listing->city,
            'district' => $listing->district,
            'property_type' => $listing->property_type,
            'listing_type' => $listing->listing_type,
            'bedrooms' => $listing->bedrooms,
            'bathrooms' => $listing->bathrooms,
            'area_sqm' => $listing->area_sqm,
            'is_active' => $listing->is_active,
            'is_featured' => $listing->is_featured,
        ];
    }
}
