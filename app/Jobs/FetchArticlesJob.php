<?php

declare(strict_types=1);

namespace App\Jobs;

use Throwable;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\NewsAggregator\NewsAggregatorService;

class FetchArticlesJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly string $provider,
        public readonly array $params = [],
    ) {}

    /**
     * Execute the job.
     */
    public function handle(NewsAggregatorService $aggregator): void
    {
        Log::info("Starting background fetch from {$this->provider}");

        $storedCount = $aggregator->fetchFromProvider($this->provider, $this->params);

        Log::info("Background fetch completed from {$this->provider}", [
            'articles_stored' => $storedCount,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error("Failed to fetch articles from {$this->provider}", [
            'error' => $exception?->getMessage(),
        ]);
    }
}
