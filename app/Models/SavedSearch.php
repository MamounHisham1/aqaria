<?php

namespace App\Models;

use App\Support\ListingFilters;
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
     * Build the query for listings matching this saved search.
     *
     * @return Builder<Listing>
     */
    public function matchingListings(): Builder
    {
        $query = Listing::query()->active();

        return ListingFilters::apply($query, $this->filters ?? []);
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
