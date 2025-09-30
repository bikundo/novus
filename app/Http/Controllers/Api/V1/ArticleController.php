<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use App\Services\Cache\ArticleCacheService;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Http\Requests\Api\V1\FilterArticlesRequest;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Articles', 'Endpoints for managing and retrieving articles')]
class ArticleController extends Controller
{
    public function __construct(
        protected readonly ArticleCacheService $cacheService,
    ) {}

    /**
     * List all articles
     *
     * Retrieve a paginated list of articles with optional filtering by source, category, author, and date range.
     * Results are cached for 15 minutes for optimal performance.
     */
    #[QueryParam('source', 'string', 'Filter by source slug', required: false, example: 'the-guardian')]
    #[QueryParam('category', 'string', 'Filter by category slug', required: false, example: 'technology')]
    #[QueryParam('author', 'string', 'Filter by author name', required: false, example: 'John Doe')]
    #[QueryParam('from', 'string', 'Filter articles from this date (YYYY-MM-DD)', required: false, example: '2025-10-01')]
    #[QueryParam('to', 'string', 'Filter articles to this date (YYYY-MM-DD)', required: false, example: '2025-10-03')]
    #[QueryParam('page', 'integer', 'Page number for pagination', required: false, example: 1)]
    #[QueryParam('per_page', 'integer', 'Number of items per page (max 100)', required: false, example: 20)]
    #[ResponseFromApiResource(ArticleResource::class, Article::class, collection: true, paginate: 20)]
    public function index(FilterArticlesRequest $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->input('per_page', config('news-aggregator.pagination.per_page', 20));

        $filters = [
            'source'   => $request->input('source'),
            'category' => $request->input('category'),
            'author'   => $request->input('author'),
            'from'     => $request->input('from'),
            'to'       => $request->input('to'),
        ];

        $articles = $this->cacheService->getArticles($filters, $perPage);

        return ArticleResource::collection($articles);
    }

    /**
     * Get a single article
     *
     * Retrieve detailed information about a specific article, including its source, categories, and authors.
     * Results are cached for 15 minutes.
     */
    #[ResponseFromApiResource(ArticleResource::class, Article::class)]
    public function show(Article $article): ArticleResource
    {
        $cachedArticle = $this->cacheService->getArticle($article->id);

        return new ArticleResource($cachedArticle ?? $article->load(['source', 'categories', 'authors']));
    }
}
