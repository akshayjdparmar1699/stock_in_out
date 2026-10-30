<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * One-off diagnostic for the "Continue with Google" re-login bug — `tinker`
 * crashes on this host (Psy Shell needs shell_exec, which Hostinger
 * disables), so this is a plain artisan command instead.
 */
#[Signature('app:debug-user {email}')]
class DebugUser extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email');
        $users = User::where('email', $email)->orWhere('email', strtolower($email))->get();

        if ($users->isEmpty()) {
            $this->warn("No user found with email: {$email}");

            return self::SUCCESS;
        }

        foreach ($users as $user) {
            $this->table(['Field', 'Value'], [
                ['id', $user->id],
                ['name', $user->name],
                ['email', $user->email],
                ['google_id', $user->google_id ?? '(null)'],
                ['has password', $user->password ? 'yes' : 'no (null)'],
                ['company_id', $user->company_id ?? '(null)'],
                ['role', $user->role],
                ['created_at', $user->created_at],
            ]);
        }

        return self::SUCCESS;
    }
}
