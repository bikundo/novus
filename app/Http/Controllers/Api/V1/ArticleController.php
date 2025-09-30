<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Http\Requests\Api\V1\FilterArticlesRequest;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Articles', 'Endpoints for managing and retrieving articles')]
class ArticleController extends Controller
{
    /**
     * List all articles
     *
     * Retrieve a paginated list of articles with optional filtering by source, category, author, and date range.
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

        $query = Article::query()
            ->with(['source', 'categories', 'authors'])
            ->latest('published_at');

        if ($request->filled('source')) {
            $query->whereHas('source', function ($q) use ($request) {
                $q->where('slug', $request->input('source'));
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        if ($request->filled('author')) {
            $query->whereHas('authors', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('author') . '%');
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('published_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('published_at', '<=', $request->input('to'));
        }

        $articles = $query->paginate($perPage);

        return ArticleResource::collection($articles);
    }

    /**
     * Get a single article
     *
     * Retrieve detailed information about a specific article including its source, categories, and authors.
     */
    #[ResponseFromApiResource(ArticleResource::class, Article::class)]
    public function show(Article $article): ArticleResource
    {
        $article->load(['source', 'categories', 'authors']);

        return new ArticleResource($article);
    }
}
