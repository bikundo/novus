<?php

declare(strict_types=1);

namespace App\Services\NewsAggregator;

use Exception;
use Carbon\Carbon;

class ArticleNormalizer
{
    /**
     * Normalize articles from different providers to a standard format.
     *
     * @param  array<int, array<string, mixed>>  $articles
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $articles, string $provider): array
    {
        return array_map(
            fn (array $article) => $this->normalizeArticle($article, $provider),
            $articles
        );
    }

    /**
     * Normalize a single article based on the provider.
     *
     * @param  array<string, mixed>  $article
     * @return array<string, mixed>
     */
    private function normalizeArticle(array $article, string $provider): array
    {
        return match ($provider) {
            'newsapi'  => $this->normalizeNewsApiArticle($article),
            'guardian' => $this->normalizeGuardianArticle($article),
            'nyt'      => $this->normalizeNytArticle($article),
            default    => [],
        };
    }

    /**
     * Normalize the NewsAPI article format.
     *
     * @param  array<string, mixed>  $article
     * @return array<string, mixed>
     */
    private function normalizeNewsApiArticle(array $article): array
    {
        return [
            'external_id'  => $this->generateExternalId('newsapi', $article['url'] ?? ''),
            'source'       => $article['source']['name'] ?? 'Unknown',
            'title'        => $article['title'] ?? '',
            'description'  => $article['description'] ?? null,
            'content'      => $article['content'] ?? null,
            'url'          => $article['url'] ?? '',
            'image_url'    => $article['urlToImage'] ?? null,
            'author'       => $this->parseAuthors($article['author'] ?? null),
            'category'     => $this->extractCategory($article),
            'published_at' => $this->parseDate($article['publishedAt'] ?? null),
        ];
    }

    /**
     * Normalize Guardian article format.
     *
     * @param  array<string, mixed>  $article
     * @return array<string, mixed>
     */
    private function normalizeGuardianArticle(array $article): array
    {
        return [
            'external_id'  => $article['id'] ?? $this->generateExternalId('guardian', $article['webUrl'] ?? ''),
            'source'       => 'The Guardian',
            'title'        => $article['webTitle'] ?? '',
            'description'  => $article['fields']['bodyText'] ?? null,
            'content'      => $article['fields']['bodyText'] ?? null,
            'url'          => $article['webUrl'] ?? '',
            'image_url'    => $article['fields']['thumbnail'] ?? null,
            'author'       => $this->parseAuthors($article['fields']['byline'] ?? null),
            'category'     => $this->extractGuardianCategory($article),
            'published_at' => $this->parseDate($article['webPublicationDate'] ?? null),
        ];
    }

    /**
     * Normalize NYT article format.
     *
     * @param  array<string, mixed>  $article
     * @return array<string, mixed>
     */
    private function normalizeNytArticle(array $article): array
    {
        $imageUrl = null;
        if (!empty($article['multimedia']) && isset($article['multimedia'][0]['url'])) {
            $imageUrl = 'https://www.nytimes.com/' . $article['multimedia'][0]['url'];
        }

        return [
            'external_id'  => $article['_id'] ?? $this->generateExternalId('nyt', $article['web_url'] ?? ''),
            'source'       => 'The New York Times',
            'title'        => $article['headline']['main'] ?? '',
            'description'  => $article['abstract'] ?? null,
            'content'      => $article['lead_paragraph'] ?? $article['snippet'] ?? null,
            'url'          => $article['web_url'] ?? '',
            'image_url'    => $imageUrl,
            'author'       => $this->parseNytAuthors($article['byline']['person'] ?? []),
            'category'     => $this->extractNytCategory($article),
            'published_at' => $this->parseDate($article['pub_date'] ?? null),
        ];
    }

    /**
     * Generate a unique external ID from provider and URL.
     */
    private function generateExternalId(string $provider, string $url): string
    {
        return $provider . '_' . md5($url);
    }

    /**
     * Parse authors from string or array.
     *
     * @param  string|array<string>|null  $authors
     * @return array<string>
     */
    private function parseAuthors(string|array|null $authors): array
    {
        if ($authors === null || $authors === '') {
            return [];
        }

        if (is_array($authors)) {
            return $authors;
        }

        return array_map('trim', explode(',', $authors));
    }

    /**
     * Parse NYT authors from person array.
     *
     * @param  array<int, array<string, mixed>>  $persons
     * @return array<string>
     */
    private function parseNytAuthors(array $persons): array
    {
        return array_map(
            fn (array $person) => trim(($person['firstname'] ?? '') . ' ' . ($person['lastname'] ?? '')),
            $persons
        );
    }

    /**
     * Extract category from article data.
     *
     * @param  array<string, mixed>  $article
     * @return array<string>
     */
    private function extractCategory(array $article): array
    {
        $categories = [];

        if (!empty($article['category'])) {
            $categories[] = $article['category'];
        }

        return $categories;
    }

    /**
     * Extract Guardian category from article data.
     *
     * @param  array<string, mixed>  $article
     * @return array<string>
     */
    private function extractGuardianCategory(array $article): array
    {
        $categories = [];

        if (!empty($article['sectionName'])) {
            $categories[] = $article['sectionName'];
        }

        if (!empty($article['pillarName']) && !in_array($article['pillarName'], $categories)) {
            $categories[] = $article['pillarName'];
        }

        return $categories;
    }

    /**
     * Extract NYT category from article data.
     *
     * @param  array<string, mixed>  $article
     * @return array<string>
     */
    private function extractNytCategory(array $article): array
    {
        $categories = [];

        if (!empty($article['section_name'])) {
            $categories[] = $article['section_name'];
        }

        if (!empty($article['news_desk'])) {
            $categories[] = $article['news_desk'];
        }

        return array_unique($categories);
    }

    /**
     * Parse date string to Carbon instance.
     */
    private function parseDate(?string $date): ?Carbon
    {
        if ($date === null || $date === '') {
            return null;
        }

        try {
            return Carbon::parse($date);
        }
        catch (Exception $exception) {
            return null;
        }
    }
}
