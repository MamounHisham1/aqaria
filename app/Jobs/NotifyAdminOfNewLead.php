<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\User;
use App\Notifications\NewLead;
use App\Services\Telegram\TelegramNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyAdminOfNewLead implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    /**
     * Notify every admin user by mail, and push a Telegram message to the
     * configured admin chat. Failure of either channel is logged but does not
     * raise, so the lead submission itself never fails because of a notify hiccup.
     */
    public function handle(TelegramNotifier $notifier): void
    {
        $admins = User::where('is_admin', true)->get();

        if ($admins->isNotEmpty()) {
            try {
                Notification::send($admins, new NewLead($this->lead));
            } catch (\Exception $e) {
                Log::error('Failed to notify admins of new lead by mail', [
                    'lead_id' => $this->lead->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        try {
            $notification = new NewLead($this->lead);
            $notifier->notifyAdmin($notification->toTelegramText());
        } catch (\Exception $e) {
            Log::error('Failed to notify admins of new lead via Telegram', [
                'lead_id' => $this->lead->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
