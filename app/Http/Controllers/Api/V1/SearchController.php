<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Group;
use App\Services\SearchAnalyticsService;
use Knuckles\Scribe\Attributes\QueryParam;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Http\Requests\Api\V1\SearchArticlesRequest;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Search', 'Full-text search for articles')]
class SearchController extends Controller
{
    public function __construct(
        protected readonly SearchAnalyticsService $analyticsService,
    ) {}

    /**
     * Search articles
     *
     * Perform a full-text search across article titles, descriptions, and content using Laravel Scout with Typesense.
     * Supports typo tolerance, relevance scoring, and advanced filters.
     */
    #[QueryParam('q', 'string', 'Search query string', required: true, example: 'climate change')]
    #[QueryParam('source', 'string', 'Filter by source slug', required: false, example: 'the-guardian')]
    #[QueryParam('category', 'string', 'Filter by category slug', required: false, example: 'technology')]
    #[QueryParam('page', 'integer', 'Page number for pagination', required: false, example: 1)]
    #[QueryParam('per_page', 'integer', 'Number of items per page (max 100)', required: false, example: 20)]
    #[ResponseFromApiResource(ArticleResource::class, Article::class, collection: true, paginate: 20)]
    public function search(SearchArticlesRequest $request): AnonymousResourceCollection
    {
        $startTime = microtime(true);

        $searchQuery = $request->input('q');
        $perPage = (int) $request->input('per_page', config('news-aggregator.pagination.per_page', 20));

        if (empty($searchQuery)) {
            $query = Article::query()
                ->with(['source', 'categories', 'authors'])
                ->latest('published_at');

            if ($request->filled('from')) {
                $query->where('published_at', '>=', $request->input('from'));
            }

            if ($request->filled('to')) {
                $query->where('published_at', '<=', $request->input('to'));
            }

            $articles = $query->paginate($perPage);

            return ArticleResource::collection($articles);
        }

        $searchBuilder = Article::search($searchQuery)
            ->options([
                'query_by' => 'title,description,content,source_name,category_names,author_names',
                'sort_by'  => 'published_at:desc',
                'per_page' => $perPage,
            ]);

        $filterBy = [];

        if ($request->filled('source')) {
            $filterBy[] = 'source_slug:=' . $request->input('source');
        }

        if ($request->filled('category')) {
            $filterBy[] = 'category_slugs:=' . $request->input('category');
        }

        if ($request->filled('from')) {
            $fromTimestamp = strtotime($request->input('from'));
            $filterBy[] = 'published_at:>=' . $fromTimestamp;
        }

        if ($request->filled('to')) {
            $toTimestamp = strtotime($request->input('to') . ' 23:59:59');
            $filterBy[] = 'published_at:<=' . $toTimestamp;
        }

        if (!empty($filterBy)) {
            $searchBuilder->options(['filter_by' => implode(' && ', $filterBy)]);
        }

        $articles = $searchBuilder
            ->query(function ($query) use ($request) {
                $query->with(['source', 'categories', 'authors']);

                if ($request->filled('from')) {
                    $query->where('published_at', '>=', $request->input('from'));
                }

                if ($request->filled('to')) {
                    $query->where('published_at', '<=', $request->input('to') . ' 23:59:59');
                }

                return $query;
            })
            ->paginate($perPage);

        $responseTime = (int) ((microtime(true) - $startTime) * 1000);

        $this->analyticsService->trackSearch(
            query: $searchQuery,
            resultsCount: $articles->total(),
            userId: auth()->id(),
            sourceFilter: $request->input('source'),
            categoryFilter: $request->input('category'),
            fromDate: $request->input('from'),
            toDate: $request->input('to'),
            responseTimeMs: $responseTime,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return ArticleResource::collection($articles);
    }
}
