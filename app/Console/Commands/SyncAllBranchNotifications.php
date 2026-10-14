<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Services\NotificationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Covers the one notification category that nothing else triggers: a
 * customer going inactive is purely about time passing, not a save
 * anyone makes. Invoice/stock/customer saves already re-sync their own
 * branch immediately, so this doesn't need to run often — it exists for
 * the branch nobody has touched in a while, which is exactly the branch
 * an inactive-customer alert matters most for.
 */
#[Signature('app:sync-all-branch-notifications')]
#[Description('Re-sync notifications (and push anything new) for every branch')]
class SyncAllBranchNotifications extends Command
{
    public function handle(): int
    {
        $branches = Branch::all();

        foreach ($branches as $branch) {
            NotificationService::sync($branch);
        }

        $this->info("Synced {$branches->count()} branch(es).");

        return self::SUCCESS;
    }
}
