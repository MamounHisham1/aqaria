<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    /**
     * Anyone (including guests) may submit a lead. Store authorization is
     * handled by the form request + controller; this method gates the admin
     * inbox views.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->is_admin || ($lead->user_id !== null && $lead->user_id === $user->id);
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->is_admin;
    }
}
