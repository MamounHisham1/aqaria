<?php

namespace App\Jobs;

use App\Models\ListingView;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordListingView implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $listingId,
        public string $visitorId,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}

    /**
     * Persist the view, deduplicating against the same visitor within 24h.
     */
    public function handle(): void
    {
        $recentView = ListingView::query()
            ->forListing($this->listingId)
            ->byVisitor($this->visitorId)
            ->where('viewed_at', '>=', now()->subDay())
            ->exists();

        if ($recentView) {
            return;
        }

        ListingView::create([
            'listing_id' => $this->listingId,
            'visitor_id' => $this->visitorId,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'viewed_at' => now(),
        ]);
    }
}
