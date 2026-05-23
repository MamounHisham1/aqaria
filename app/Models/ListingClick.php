<?php

namespace App\Models;

use Database\Factories\ListingClickFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingClick extends Model
{
    /** @use HasFactory<ListingClickFactory> */
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'visitor_id',
        'click_type',
        'clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function scopeForListing($query, int $listingId)
    {
        return $query->where('listing_id', $listingId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('click_type', $type);
    }

    public function scopeInLastDays($query, int $days)
    {
        return $query->where('clicked_at', '>=', now()->subDays($days));
    }
}
