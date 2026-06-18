<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\In;

/**
 * Single source of truth for the listing search filters.
 *
 * The valid filter keys, their validation rules, and how each maps to a
 * Listing query scope used to be duplicated across the listings controller,
 * the saved-search controller, and the SavedSearch model. Adding a filter
 * meant touching all three. This object owns all three concerns so a change
 * here is the only change needed.
 *
 * @template TModel of \App\Models\Listing
 */
class ListingFilters
{
    /**
     * The allowed filter keys, in canonical order.
     *
     * @return list<string>
     */
    public static function keys(): array
    {
        return [
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
        ];
    }

    /**
     * Validation rules for the filter payload (dot-notated under "filters.*").
     *
     * @return array<string, array<int, string|In>>
     */
    public static function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:200'],
            'city' => ['nullable', 'string', 'max:100'],
            'listing_type' => ['nullable', 'in:sale,rent'],
            'property_type' => ['nullable', 'in:apartment,villa,townhouse,commercial'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'min_area' => ['nullable', 'integer', 'min:0'],
            'max_area' => ['nullable', 'integer', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Build a normalized Collection of non-empty filters from a raw input bag.
     *
     * @param  array<string, mixed>|Collection  $input
     * @return Collection<string, mixed>
     */
    public static function normalize(array|Collection $input): Collection
    {
        return collect($input)->only(self::keys())->filter(fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Apply the given filters to a Listing query builder.
     *
     * @param  Builder<TModel>  $query
     * @param  array<string, mixed>|Collection  $filters
     * @return Builder<TModel>
     */
    public static function apply(Builder $query, array|Collection $filters): Builder
    {
        $filters = self::normalize($filters);

        $query->when($filters->get('q'), fn ($q, $v) => $q->search($v))
            ->when($filters->get('city'), fn ($q, $v) => $q->inCity($v))
            ->when($filters->get('listing_type'), fn ($q, $v) => $q->byListingType($v))
            ->when($filters->get('property_type'), fn ($q, $v) => $q->byPropertyType($v))
            ->when($filters->get('min_price') || $filters->get('max_price'), function ($q) use ($filters) {
                $q->priceBetween($filters->get('min_price'), $filters->get('max_price'));
            })
            ->when($filters->get('min_area') || $filters->get('max_area'), function ($q) use ($filters) {
                $q->areaBetween($filters->get('min_area'), $filters->get('max_area'));
            })
            ->when($filters->get('bedrooms'), fn ($q, $v) => $q->withBedrooms((int) $v))
            ->when($filters->get('bathrooms'), fn ($q, $v) => $q->withBathrooms((int) $v));

        return $query;
    }
}
