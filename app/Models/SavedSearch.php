<?php

namespace App\Models;

use Database\Factories\SavedSearchFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    /** @use HasFactory<SavedSearchFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'filters',
        'notify',
        'last_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'notify' => 'boolean',
            'last_notified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Apply saved search filters to a Listing query.
     *
     * @param  Builder<Listing>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Listing>
     */
    public static function applyFiltersToQuery(Builder $query, array $filters): Builder
    {
        $filters = collect($filters)->filter();

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

    /**
     * Build the query for listings matching this saved search.
     *
     * @return Builder<Listing>
     */
    public function matchingListings(): Builder
    {
        $query = Listing::query()->active();

        return static::applyFiltersToQuery($query, $this->filters ?? []);
    }

    /**
     * Listings created since the last notification (or all if never notified).
     */
    public function newMatchingListings()
    {
        $query = $this->matchingListings();

        if ($this->last_notified_at) {
            $query->where('created_at', '>', $this->last_notified_at);
        }

        return $query->get();
    }
}
