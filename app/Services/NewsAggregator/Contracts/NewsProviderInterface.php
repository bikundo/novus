<?php

declare(strict_types=1);

namespace App\Services\NewsAggregator\Contracts;

interface NewsProviderInterface
{
    /**
     * Fetch articles from the news provider.
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function fetchArticles(array $params = []): array;

    /**
     * Search for articles based on a query.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function searchArticles(string $query, array $filters = []): array;

    /**
     * Get the provider name.
     */
    public function getProviderName(): string;

    /**
     * Get available sources from the provider.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSources(): array;
}
