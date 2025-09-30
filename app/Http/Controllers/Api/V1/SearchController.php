<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Http\Requests\Api\V1\SearchArticlesRequest;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Search', 'Full-text search for articles')]
class SearchController extends Controller
{
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
        $searchQuery = $request->input('q');
        $perPage = (int) $request->input('per_page', config('news-aggregator.pagination.per_page', 20));

        if (empty($searchQuery)) {
            $articles = Article::query()
                ->with(['source', 'categories', 'authors'])
                ->latest('published_at')
                ->paginate($perPage);

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

        if (!empty($filterBy)) {
            $searchBuilder->options(['filter_by' => implode(' && ', $filterBy)]);
        }

        $articles = $searchBuilder
            ->query(fn ($query) => $query->with(['source', 'categories', 'authors']))
            ->paginate($perPage);

        return ArticleResource::collection($articles);
    }
}
