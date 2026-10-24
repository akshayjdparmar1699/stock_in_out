<?php

use App\Models\Branch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * The "Scan and pay" block on a bill used to fall back to this single
     * branch's own real bank account + QR whenever a branch had none of its
     * own set — originally harmless when this was the only branch, but it
     * meant every new branch's customers saw (and could pay into) this
     * branch's account by default. That fallback is now removed, so this
     * backfills this one branch's own fields with what the fallback used
     * to show, since it's the one branch the account genuinely belongs to.
     * Every other branch simply shows no payment block until its own admin
     * sets one up.
     */
    public function up(): void
    {
        $branches = Branch::whereNull('bank_name')
            ->where('name', 'like', '%Om Sai%Aalubhandar%')
            ->get();

        foreach ($branches as $branch) {
            $branch->bank_name = 'HDFC Bank';
            $branch->account_number = '50200097420397';
            $branch->account_holder_name = 'Om Sai Aalubhandar';
            $branch->ifsc_code = 'HDFC0004196';

            if (! $branch->qr_code_path) {
                $sourcePath = public_path('images/payment-qr.png');

                if (File::exists($sourcePath)) {
                    $storedPath = 'branch-qr-codes/'.uniqid('legacy-').'.png';
                    Storage::disk('public')->put($storedPath, File::get($sourcePath));
                    $branch->qr_code_path = $storedPath;
                }
            }

            $branch->save();
        }
    }

    /**
     * Not reversible — there's no way to tell whether a branch's bank_name
     * etc. were genuinely blank before this ran or just hadn't been set
     * yet, so rolling back would risk deleting real data.
     */
    public function down(): void {}
};
