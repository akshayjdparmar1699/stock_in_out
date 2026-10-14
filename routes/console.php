<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Catches inactive-customer alerts on a branch nobody has otherwise
// touched recently — every other notification category already re-syncs
// itself the moment a relevant invoice/stock/customer save happens.
Schedule::command('app:sync-all-branch-notifications')->everyThirtyMinutes();
