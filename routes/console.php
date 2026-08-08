<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Scheduled Monthly 50:50 Payout to IPPTI & Benlaris on 1st of every month at 00:01 WIB
Schedule::command('finance:monthly-payout')->monthlyOn(1, '00:01');

