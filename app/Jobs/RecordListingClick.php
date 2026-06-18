<?php

namespace App\Jobs;

use App\Models\ListingClick;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordListingClick implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $listingId,
        public string $visitorId,
        public string $clickType,
    ) {}

    /**
     * Persist the click event.
     */
    public function handle(): void
    {
        ListingClick::create([
            'listing_id' => $this->listingId,
            'visitor_id' => $this->visitorId,
            'click_type' => $this->clickType,
            'clicked_at' => now(),
        ]);
    }
}
