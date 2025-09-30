<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Models\Author;
use App\Models\Source;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArticleCacheService
{
    private const int TTL_ARTICLES = 900;

    private const int TTL_SOURCES = 86400;

    private const int TTL_CATEGORIES = 86400;

    private const int TTL_AUTHORS = 3600;

    private const string TAG_ARTICLES = 'articles';

    private const string TAG_SOURCES = 'sources';

    private const string TAG_CATEGORIES = 'categories';

    private const string TAG_AUTHORS = 'authors';

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get cached articles with filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getArticles(array $filters, int $perPage): LengthAwarePaginator
    {
        $cacheKey = $this->buildArticlesCacheKey($filters, $perPage);

        return Cache::tags([self::TAG_ARTICLES])
            ->remember($cacheKey, self::TTL_ARTICLES, function () use ($filters, $perPage) {
                return $this->queryArticles($filters, $perPage);
            });
    }

    /**
     * Get a single cached article.
     */
    public function getArticle(int $articleId): ?Article
    {
        $cacheKey = "article:{$articleId}";

        return Cache::tags([self::TAG_ARTICLES])
            ->remember($cacheKey, self::TTL_ARTICLES, function () use ($articleId) {
                return Article::query()
                    ->with(['source', 'categories', 'authors'])
                    ->find($articleId);
            });
    }

    /**
     * Get cached sources.
     */
    public function getSources(): Collection
    {
        return Cache::tags([self::TAG_SOURCES])
            ->remember('sources:all', self::TTL_SOURCES, function () {
                return Source::query()
                    ->orderBy('name')
                    ->get();
            });
    }

    /**
     * Get cached categories.
     */
    public function getCategories(): Collection
    {
        return Cache::tags([self::TAG_CATEGORIES])
            ->remember('categories:all', self::TTL_CATEGORIES, function () {
                return Category::query()
                    ->orderBy('name')
                    ->get();
            });
    }

    /**
     * Get cached authors with pagination.
     */
    public function getAuthors(int $perPage): LengthAwarePaginator
    {
        $page = request()->input('page', 1);
        $cacheKey = "authors:page:{$page}:per_page:{$perPage}";

        return Cache::tags([self::TAG_AUTHORS])
            ->remember($cacheKey, self::TTL_AUTHORS, function () use ($perPage) {
                return Author::query()
                    ->orderBy('name')
                    ->paginate($perPage);
            });
    }

    /**
     * Invalidate article caches.
     */
    public function invalidateArticles(): void
    {
        Cache::tags([self::TAG_ARTICLES])->flush();
    }

    /**
     * Invalidate source caches.
     */
    public function invalidateSources(): void
    {
        Cache::tags([self::TAG_SOURCES])->flush();
    }

    /**
     * Invalidate category caches.
     */
    public function invalidateCategories(): void
    {
        Cache::tags([self::TAG_CATEGORIES])->flush();
    }

    /**
     * Invalidate author caches.
     */
    public function invalidateAuthors(): void
    {
        Cache::tags([self::TAG_AUTHORS])->flush();
    }

    /**
     * Invalidate all caches.
     */
    public function invalidateAll(): void
    {
        Cache::tags([
            self::TAG_ARTICLES,
            self::TAG_SOURCES,
            self::TAG_CATEGORIES,
            self::TAG_AUTHORS,
        ])->flush();
    }

    /**
     * Build cache key for articles query.
     *
     * @param  array<string, mixed>  $filters
     */
    private function buildArticlesCacheKey(array $filters, int $perPage): string
    {
        $page = request()->input('page', 1);
        $keyParts = [
            'articles',
            'page:' . $page,
            'per_page:' . $perPage,
        ];

        foreach ($filters as $key => $value) {
            if ($value !== null) {
                $keyParts[] = "{$key}:{$value}";
            }
        }

        return implode(':', $keyParts);
    }

    /**
     * Query articles from database.
     *
     * @param  array<string, mixed>  $filters
     */
    private function queryArticles(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Article::query()
            ->with(['source', 'categories', 'authors'])
            ->latest('published_at');

        if (isset($filters['source'])) {
            $query->whereHas('source', function ($q) use ($filters) {
                $q->where('slug', $filters['source']);
            });
        }

        if (isset($filters['category'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        if (isset($filters['author'])) {
            $query->whereHas('authors', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['author'] . '%');
            });
        }

        if (isset($filters['from'])) {
            $query->whereDate('published_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('published_at', '<=', $filters['to']);
        }

        return $query->paginate($perPage);
    }
}
