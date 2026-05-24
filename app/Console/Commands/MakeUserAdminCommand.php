<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdminCommand extends Command
{
    protected $signature = 'user:make-admin {email : The email of the user to promote}';

    protected $description = 'Promote a user to admin';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");

            return self::FAILURE;
        }

        if ($user->is_admin) {
            $this->info("User {$email} is already an admin.");

            return self::SUCCESS;
        }

        $user->update(['is_admin' => true]);

        $this->info("User {$email} has been promoted to admin.");

        return self::SUCCESS;
    }
}
