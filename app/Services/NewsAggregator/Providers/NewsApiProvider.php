<?php

declare(strict_types=1);

namespace App\Services\NewsAggregator\Providers;

use RuntimeException;
use App\Models\ApiLog;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;
use App\Services\NewsAggregator\Contracts\NewsProviderInterface;

class NewsApiProvider implements NewsProviderInterface
{
    private string $apiKey;

    private string $baseUrl;

    private Client $client;

    public function __construct()
    {
        $this->apiKey = config('news-aggregator.providers.newsapi.api_key') ?? '';
        $this->baseUrl = config('news-aggregator.providers.newsapi.base_url');

        if (empty($this->apiKey)) {
            throw new RuntimeException('NewsAPI API key is not configured. Please set NEWSAPI_KEY in your .env file.');
        }

        $this->client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout'  => 30,
        ]);
    }

    /**
     * Fetch articles from NewsAPI.
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function fetchArticles(array $params = []): array
    {
        $startTime = microtime(true);

        try {
            $defaultParams = [
                'apiKey'   => $this->apiKey,
                'language' => 'en',
                'pageSize' => config('news-aggregator.max_results', 100),
                'sortBy'   => 'publishedAt',
            ];

            if (empty($params['q']) && empty($params['sources']) && empty($params['domains'])) {
                $defaultParams['q'] = 'technology OR business OR sports';
            }

            $queryParams = array_merge($defaultParams, $params);

            $response = $this->client->get('everything', [
                'query' => $queryParams,
            ]);

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $data = json_decode($response->getBody()->getContents(), true);
            $articles = $data['articles'] ?? [];

            $this->logApiCall('everything', 200, $responseTime, count($articles));

            return $articles;
        }
        catch (GuzzleException $exception) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $statusCode = $exception->getCode() ?: 500;

            $this->logApiCall('everything', $statusCode, $responseTime, 0, $exception->getMessage());

            Log::error('NewsAPI fetch failed', [
                'provider' => $this->getProviderName(),
                'error'    => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Search for articles based on a query.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function searchArticles(string $query, array $filters = []): array
    {
        $params = array_merge(['q' => $query], $filters);

        return $this->fetchArticles($params);
    }

    /**
     * Get the provider name.
     */
    public function getProviderName(): string
    {
        return 'newsapi';
    }

    /**
     * Get available sources from NewsAPI.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSources(): array
    {
        $startTime = microtime(true);

        try {
            $response = $this->client->get('/sources', [
                'query' => [
                    'apiKey'   => $this->apiKey,
                    'language' => 'en',
                ],
            ]);

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $data = json_decode($response->getBody()->getContents(), true);
            $sources = $data['sources'] ?? [];

            $this->logApiCall('/sources', 200, $responseTime, count($sources));

            return $sources;
        }
        catch (GuzzleException $exception) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $statusCode = $exception->getCode() ?: 500;

            $this->logApiCall('/sources', $statusCode, $responseTime, 0, $exception->getMessage());

            Log::error('NewsAPI sources fetch failed', [
                'provider' => $this->getProviderName(),
                'error'    => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Log the API call to the database.
     */
    private function logApiCall(
        string $endpoint,
        int $statusCode,
        int $responseTime,
        int $articlesFetched = 0,
        ?string $errorMessage = null
    ): void {
        ApiLog::create([
            'api_provider'     => $this->getProviderName(),
            'endpoint'         => $endpoint,
            'status_code'      => $statusCode,
            'response_time'    => $responseTime,
            'articles_fetched' => $articlesFetched,
            'error_message'    => $errorMessage,
        ]);
    }
}
