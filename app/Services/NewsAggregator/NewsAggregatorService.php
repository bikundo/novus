<?php

declare(strict_types=1);

namespace App\Services\NewsAggregator;

use RuntimeException;
use Illuminate\Support\Facades\Log;
use App\Services\ArticleStorageService;
use App\Services\NewsAggregator\Providers\NewsApiProvider;
use App\Services\NewsAggregator\Providers\GuardianProvider;
use App\Services\NewsAggregator\Providers\NewYorkTimesProvider;
use App\Services\NewsAggregator\Contracts\NewsProviderInterface;

class NewsAggregatorService
{
    /**
     * @var array<string, NewsProviderInterface>
     */
    private array $providers = [];

    public function __construct(
        private readonly ArticleNormalizer $normalizer,
        private readonly ArticleStorageService $storage,
    ) {
        $this->registerProviders();
    }

    /**
     * Register all available news providers.
     */
    private function registerProviders(): void
    {
        if (config('news-aggregator.providers.newsapi.enabled')) {
            try {
                $this->providers['newsapi'] = new NewsApiProvider();
            }
            catch (RuntimeException $exception) {
                Log::warning('NewsAPI provider not available: ' . $exception->getMessage());
            }
        }

        if (config('news-aggregator.providers.guardian.enabled')) {
            try {
                $this->providers['guardian'] = new GuardianProvider();
            }
            catch (RuntimeException $exception) {
                Log::warning('Guardian provider not available: ' . $exception->getMessage());
            }
        }

        if (config('news-aggregator.providers.nyt.enabled')) {
            try {
                $this->providers['nyt'] = new NewYorkTimesProvider();
            }
            catch (RuntimeException $exception) {
                Log::warning('NYT provider not available: ' . $exception->getMessage());
            }
        }
    }

    /**
     * Fetch articles from all enabled providers.
     *
     * @param  array<string, mixed>  $params
     * @return int Total number of articles stored
     */
    public function fetchFromAllProviders(array $params = []): int
    {
        $totalStored = 0;

        foreach ($this->providers as $providerName => $provider) {
            $stored = $this->fetchFromProvider($providerName, $params);
            $totalStored += $stored;
        }

        return $totalStored;
    }

    /**
     * Fetch articles from a specific provider.
     *
     * @param  array<string, mixed>  $params
     * @return int Number of articles stored
     */
    public function fetchFromProvider(string $providerName, array $params = []): int
    {
        if (!isset($this->providers[$providerName])) {
            Log::warning("Provider not found: {$providerName}");

            return 0;
        }

        $provider = $this->providers[$providerName];

        Log::info("Fetching articles from {$providerName}");

        $articles = $provider->fetchArticles($params);

        if (empty($articles)) {
            Log::info("No articles fetched from {$providerName}");

            return 0;
        }

        $normalizedArticles = $this->normalizer->normalize($articles, $providerName);

        $storedCount = $this->storage->storeArticles($normalizedArticles);

        Log::info("Stored {$storedCount} articles from {$providerName}");

        return $storedCount;
    }

    /**
     * Search articles across all providers.
     *
     * @param  array<string, mixed>  $filters
     * @return int Number of articles stored
     */
    public function searchAcrossProviders(string $query, array $filters = []): int
    {
        $totalStored = 0;

        foreach ($this->providers as $providerName => $provider) {
            $articles = $provider->searchArticles($query, $filters);

            if (empty($articles)) {
                continue;
            }

            $normalizedArticles = $this->normalizer->normalize($articles, $providerName);
            $storedCount = $this->storage->storeArticles($normalizedArticles);
            $totalStored += $storedCount;

            Log::info("Stored {$storedCount} articles from {$providerName} for query: {$query}");
        }

        return $totalStored;
    }

    /**
     * Get all registered providers.
     *
     * @return array<string, NewsProviderInterface>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Get a specific provider by name.
     */
    public function getProvider(string $providerName): ?NewsProviderInterface
    {
        return $this->providers[$providerName] ?? null;
    }
}
