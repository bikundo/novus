<?php

declare(strict_types=1);

namespace App\Jobs;

use Throwable;
use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CleanupOldArticlesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 60;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $retentionDays = config('news-aggregator.retention_days', 90);
        $cutoffDate = now()->subDays($retentionDays);

        Log::info("Starting cleanup of articles older than {$retentionDays} days");

        $deletedCount = Article::where('published_at', '<', $cutoffDate)->delete();

        Log::info('Cleanup completed', [
            'deleted_count' => $deletedCount,
            'cutoff_date'   => $cutoffDate->toDateTimeString(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to cleanup old articles', [
            'error' => $exception?->getMessage(),
        ]);
    }
}
