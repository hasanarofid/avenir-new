<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('market:fetch')->everyMinute();
Schedule::command('news:fetch-rss')->hourly();

// // Sync Sectors API data setiap hari jam 17:00 WIB (10:00 UTC) — setelah market close
// // + jam 07:00 WIB (00:00 UTC) untuk pre-market brief
// Schedule::command('sectors:sync')->dailyAt('10:00')->timezone('UTC');
// Schedule::command('sectors:sync')->dailyAt('00:00')->timezone('UTC');

// // Buat draft Desk Brief setiap hari pada jam 17:05 WIB (10:05 UTC)
// // Sinkronisasi data dan pembuatan draft Desk Brief diulang setiap 15 menit
// // Antara jam 17:00 hingga 19:00 WIB.
// // Jika di jam 17:00 data Sectors belum keluar, ia akan mencoba lagi jam 17:15, 17:30, dst.
// // Jika draft hari ini sudah terbuat, ia akan otomatis melewati proses (skip).
// Schedule::command('deskbrief:sync')
//     ->everyFifteenMinutes()
//     ->between('17:00', '19:00')
//     ->timezone('Asia/Jakarta');

// Schedule::command('deskbrief:draft')
//     ->everyFifteenMinutes()
//     ->between('17:00', '19:00')
//     ->timezone('Asia/Jakarta');
