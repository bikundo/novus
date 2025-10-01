<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SearchAnalytic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SearchAnalyticsService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Track a search query with its metadata.
     */
    public function trackSearch(
        string $query,
        int $resultsCount,
        ?int $userId = null,
        ?string $sourceFilter = null,
        ?string $categoryFilter = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $responseTimeMs = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): SearchAnalytic {
        return SearchAnalytic::create([
            'user_id'          => $userId,
            'query'            => $query,
            'results_count'    => $resultsCount,
            'source_filter'    => $sourceFilter,
            'category_filter'  => $categoryFilter,
            'from_date'        => $fromDate,
            'to_date'          => $toDate,
            'response_time_ms' => $responseTimeMs,
            'ip_address'       => $ipAddress,
            'user_agent'       => $userAgent,
        ]);
    }

    /**
     * Get the most popular search queries.
     */
    public function getPopularQueries(int $limit = 10): Collection
    {
        return SearchAnalytic::query()
            ->select('query', DB::raw('COUNT(*) as search_count'))
            ->groupBy('query')
            ->orderByDesc('search_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get queries with no results (potential content gaps).
     */
    public function getNoResultQueries(int $limit = 10): Collection
    {
        return SearchAnalytic::query()
            ->select('query', DB::raw('COUNT(*) as search_count'))
            ->where('results_count', 0)
            ->groupBy('query')
            ->orderByDesc('search_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get average response time for searches.
     */
    public function getAverageResponseTime(): float
    {
        return (float) SearchAnalytic::query()
            ->whereNotNull('response_time_ms')
            ->avg('response_time_ms');
    }

    /**
     * Get search statistics for a date range.
     */
    public function getSearchStats(?string $fromDate = null, ?string $toDate = null): array
    {
        $query = SearchAnalytic::query();

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->where('created_at', '<=', $toDate);
        }

        return [
            'total_searches'           => $query->count(),
            'unique_queries'           => $query->distinct('query')->count('query'),
            'avg_results_count'        => $query->avg('results_count'),
            'avg_response_time_ms'     => $query->avg('response_time_ms'),
            'searches_with_no_results' => $query->where('results_count', 0)->count(),
        ];
    }

    /**
     * Get trending search queries (queries with increasing frequency).
     */
    public function getTrendingQueries(int $days = 7, int $limit = 10): Collection
    {
        return SearchAnalytic::query()
            ->select('query', DB::raw('COUNT(*) as search_count'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('query')
            ->orderByDesc('search_count')
            ->limit($limit)
            ->get();
    }
}
