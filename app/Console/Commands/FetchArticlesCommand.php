<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsAggregator\NewsAggregatorService;

class FetchArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch {--provider= : Specific provider to fetch from (newsapi, guardian, nyt)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from news providers and store them in the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $provider = $this->option('provider');

        $this->info('Starting article fetch...');

        if ($provider) {
            $this->fetchFromSpecificProvider($provider);
        }
        else {
            $this->fetchFromAllProviders();
        }

        $this->info('Article fetch completed!');

        return self::SUCCESS;
    }

    /**
     * Fetch articles from all providers.
     */
    private function fetchFromAllProviders(): void
    {
        $this->info('Fetching from all enabled providers...');

        $totalStored = $this->aggregator->fetchFromAllProviders();

        $this->info("Total articles stored: {$totalStored}");
    }

    /**
     * Fetch articles from a specific provider.
     */
    private function fetchFromSpecificProvider(string $provider): void
    {
        $this->info("Fetching from {$provider}...");

        $storedCount = $this->aggregator->fetchFromProvider($provider);

        if ($storedCount > 0) {
            $this->info("Stored {$storedCount} articles from {$provider}");
        }
        else {
            $this->warn("No articles stored from {$provider}");
        }
    }

    public function __construct(
        private readonly NewsAggregatorService $aggregator,
    ) {
        parent::__construct();
    }
}
