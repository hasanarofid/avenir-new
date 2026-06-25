<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('market:fetch')->everyMinute();
Schedule::command('news:fetch-rss')->hourly();

// Sync Sectors API data setiap hari jam 17:00 WIB (10:00 UTC) — setelah market close
// + jam 07:00 WIB (00:00 UTC) untuk pre-market brief
Schedule::command('sectors:sync')->dailyAt('10:00')->timezone('UTC');
Schedule::command('sectors:sync')->dailyAt('00:00')->timezone('UTC');
