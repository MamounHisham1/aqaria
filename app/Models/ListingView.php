<?php

namespace App\Models;

use Database\Factories\ListingViewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingView extends Model
{
    /** @use HasFactory<ListingViewFactory> */
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'visitor_id',
        'ip_address',
        'user_agent',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
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

    public function scopeByVisitor($query, string $visitorId)
    {
        return $query->where('visitor_id', $visitorId);
    }

    public function scopeInLastDays($query, int $days)
    {
        return $query->where('viewed_at', '>=', now()->subDays($days));
    }
}
