<?php

declare(strict_types=1);

namespace App\Jobs;

use Throwable;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchArticlesFromAllSourcesJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting fetch from all news sources');

        $providers = $this->getEnabledProviders();

        if (empty($providers)) {
            Log::warning('No enabled providers found for fetching articles');

            return;
        }

        $jobs = collect($providers)->map(function ($provider) {
            return new FetchArticlesJob($provider);
        })->all();

        $batch = Bus::batch($jobs)
            ->name('Fetch Articles from All Providers')
            ->allowFailures()
            ->onQueue('default')
            ->then(function () {
                Log::info('All provider fetch jobs completed successfully');
            })
            ->catch(function () {
                Log::error('One or more provider fetch jobs failed');
            })
            ->finally(function () {
                Log::info('Batch processing finished for all providers');
            })
            ->dispatch();

        Log::info('Dispatched batch for all providers', [
            'batch_id'  => $batch->id,
            'providers' => $providers,
            'job_count' => count($jobs),
        ]);
    }

    /**
     * Get list of enabled providers from configuration.
     *
     * @return array<int, string>
     */
    private function getEnabledProviders(): array
    {
        $providers = config('news-aggregator.providers', []);

        return collect($providers)
            ->filter(fn ($config) => $config['enabled'] ?? false)
            ->keys()
            ->values()
            ->all();
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to dispatch fetch jobs for all sources', [
            'error' => $exception?->getMessage(),
        ]);
    }
}
