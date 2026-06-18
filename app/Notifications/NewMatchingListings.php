<?php

namespace App\Notifications;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class NewMatchingListings extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection<int, Listing>  $listings
     */
    public function __construct(public Collection $listings, public string $searchName) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("New listings matching \"{$this->searchName}\"")
            ->greeting('New properties matched your saved search')
            ->line("We found {$this->listings->count()} new listing(s) matching \"{$this->searchName}\":");

        foreach ($this->listings->take(5) as $listing) {
            $mail->line("• {$listing->title} — {$listing->formatted_price} ({$listing->city})");
        }

        return $mail->action('View all matches', url('/listings'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'search_name' => $this->searchName,
            'count' => $this->listings->count(),
            'listing_ids' => $this->listings->pluck('id')->all(),
        ];
    }
}
