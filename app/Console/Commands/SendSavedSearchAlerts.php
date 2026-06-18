<?php

namespace App\Console\Commands;

use App\Models\SavedSearch;
use App\Notifications\NewMatchingListings;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('app:send-saved-search-alerts')]
#[Description('Email users about new listings matching their saved searches (with notify enabled).')]
class SendSavedSearchAlerts extends Command
{
    public function handle(): int
    {
        $sent = 0;

        SavedSearch::where('notify', true)
            ->with('user')
            ->chunkById(100, function ($searches) use (&$sent) {
                foreach ($searches as $search) {
                    $sent += $this->processSearch($search);
                }
            });

        $this->info("Sent {$sent} saved-search alert(s).");

        return self::SUCCESS;
    }

    /**
     * Email the user about new matches (if any) and stamp last_notified_at.
     */
    protected function processSearch(SavedSearch $search): int
    {
        if (! $search->user) {
            return 0;
        }

        try {
            $matches = $search->newMatchingListings();
        } catch (\Exception $e) {
            Log::error('Saved search match failed', [
                'saved_search_id' => $search->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }

        if ($matches->isEmpty()) {
            return 0;
        }

        $search->user->notify(new NewMatchingListings($matches, $search->name));
        $search->update(['last_notified_at' => now()]);

        return 1;
    }
}
