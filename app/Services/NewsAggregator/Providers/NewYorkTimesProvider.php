<?php

declare(strict_types=1);

namespace App\Services\NewsAggregator\Providers;

use RuntimeException;
use App\Models\ApiLog;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;
use App\Services\NewsAggregator\Contracts\NewsProviderInterface;

class NewYorkTimesProvider implements NewsProviderInterface
{
    private string $apiKey;

    private string $baseUrl;

    private Client $client;

    public function __construct()
    {
        $this->apiKey = config('news-aggregator.providers.nyt.api_key') ?? '';
        $this->baseUrl = config('news-aggregator.providers.nyt.base_url');

        if (empty($this->apiKey)) {
            throw new RuntimeException('NYT API key is not configured. Please set NYT_API_KEY in your .env file.');
        }

        $this->client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout'  => 30,
        ]);
    }

    /**
     * Fetch articles from The New York Times.
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function fetchArticles(array $params = []): array
    {
        $startTime = microtime(true);

        try {
            $queryParams = array_merge([
                'api-key' => $this->apiKey,
                'sort'    => 'newest',
            ], $params);

            $response = $this->client->get('search/v2/articlesearch.json', [
                'query' => $queryParams,
            ]);

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $data = json_decode($response->getBody()->getContents(), true);
            $articles = $data['response']['docs'] ?? [];

            $this->logApiCall('search/v2/articlesearch.json', 200, $responseTime, count($articles));

            return $articles;
        }
        catch (GuzzleException $exception) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $statusCode = $exception->getCode() ?: 500;

            $this->logApiCall('search/v2/articlesearch.json', $statusCode, $responseTime, 0, $exception->getMessage());

            Log::error('NYT API fetch failed', [
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
        return 'nyt';
    }

    /**
     * Get available sources from The New York Times.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSources(): array
    {
        return [
            [
                'id'          => 'nyt',
                'name'        => 'The New York Times',
                'description' => 'American newspaper based in New York City',
                'url'         => 'https://www.nytimes.com',
                'category'    => 'general',
                'language'    => 'en',
                'country'     => 'us',
            ],
        ];
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
