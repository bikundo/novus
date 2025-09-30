<?php

declare(strict_types=1);

use App\Jobs\CleanupOldArticlesJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\FetchArticlesFromAllSourcesJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new FetchArticlesFromAllSourcesJob())
    ->hourly()
    ->withoutOverlapping()
    ->name('fetch-articles-hourly')
    ->onSuccess(function () {
        Log::info('Hourly article fetch completed successfully');
    })
    ->onFailure(function () {
        Log::error('Hourly article fetch failed');
    });

Schedule::job(new CleanupOldArticlesJob())
    ->daily()
    ->at('02:00')
    ->name('cleanup-old-articles')
    ->onSuccess(function () {
        Log::info('Daily cleanup completed successfully');
    })
    ->onFailure(function () {
        Log::error('Daily cleanup failed');
    });
