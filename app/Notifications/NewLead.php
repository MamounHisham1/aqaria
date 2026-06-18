<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLead extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $listing = $this->lead->listing;

        return (new MailMessage)
            ->subject('New lead: '.$this->lead->name)
            ->greeting('New inquiry received')
            ->line("From: {$this->lead->name} ({$this->lead->phone})")
            ->when($this->lead->email, fn ($m) => $m->line("Email: {$this->lead->email}"))
            ->when($listing, fn ($m) => $m->line("Listing: {$listing->title}"))
            ->when($this->lead->message, fn ($m) => $m->line("Message: {$this->lead->message}"))
            ->action('View lead', url('/admin/leads/'.$this->lead->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'name' => $this->lead->name,
            'phone' => $this->lead->phone,
            'listing_id' => $this->lead->listing_id,
        ];
    }

    /**
     * Render the lead as a short HTML message suitable for Telegram.
     */
    public function toTelegramText(): string
    {
        $listing = $this->lead->listing;
        $price = $listing?->formatted_price;

        return collect([
            '<b>New lead received</b>',
            "Name: {$this->lead->name}",
            "Phone: {$this->lead->phone}",
            $this->lead->email ? "Email: {$this->lead->email}" : null,
            $listing ? "Listing: {$listing->title}" : null,
            $price ? "Price: {$price}" : null,
            $this->lead->message ? "Message: {$this->lead->message}" : null,
        ])->filter()->implode("\n");
    }
}
