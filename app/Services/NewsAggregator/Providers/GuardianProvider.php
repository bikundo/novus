<?php

declare(strict_types=1);

namespace App\Services\NewsAggregator\Providers;

use RuntimeException;
use App\Models\ApiLog;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;
use App\Services\NewsAggregator\Contracts\NewsProviderInterface;

class GuardianProvider implements NewsProviderInterface
{
    private string $apiKey;

    private string $baseUrl;

    private Client $client;

    public function __construct()
    {
        $this->apiKey = config('news-aggregator.providers.guardian.api_key') ?? '';
        $this->baseUrl = config('news-aggregator.providers.guardian.base_url');

        if (empty($this->apiKey)) {
            throw new RuntimeException('Guardian API key is not configured. Please set GUARDIAN_API_KEY in your .env file.');
        }

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 30,
        ]);
    }

    /**
     * Fetch articles from The Guardian.
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function fetchArticles(array $params = []): array
    {
        $startTime = microtime(true);

        try {
            $queryParams = array_merge([
                'api-key'     => $this->apiKey,
                'page-size'   => config('news-aggregator.max_results', 100),
                'order-by'    => 'newest',
                'show-fields' => 'thumbnail,bodyText,byline',
            ], $params);

            $response = $this->client->get('/search', [
                'query' => $queryParams,
            ]);

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $data = json_decode($response->getBody()->getContents(), true);
            $articles = $data['response']['results'] ?? [];

            $this->logApiCall('/search', 200, $responseTime, count($articles));

            return $articles;
        }
        catch (GuzzleException $exception) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $statusCode = $exception->getCode() ?: 500;

            $this->logApiCall('/search', $statusCode, $responseTime, 0, $exception->getMessage());

            Log::error('Guardian API fetch failed', [
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
        return 'guardian';
    }

    /**
     * Get available sources from The Guardian.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSources(): array
    {
        return [
            [
                'id'          => 'the-guardian',
                'name'        => 'The Guardian',
                'description' => 'British daily newspaper',
                'url'         => 'https://www.theguardian.com',
                'category'    => 'general',
                'language'    => 'en',
                'country'     => 'gb',
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
