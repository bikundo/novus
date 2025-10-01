<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Author;
use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use App\Services\Cache\ArticleCacheService;
use App\Http\Resources\Api\V1\AuthorResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

#[Group('Authors', 'Endpoints for managing article authors')]
class AuthorController extends Controller
{
    public function __construct(
        protected readonly ArticleCacheService $cacheService,
    ) {}

    /**
     * List all authors
     *
     * Retrieve a paginated list of all authors in the system.
     * Results are cached for 1 hour.
     */
    #[QueryParam('page', 'integer', 'Page number for pagination', required: false, example: 1)]
    #[QueryParam('per_page', 'integer', 'Number of items per page (max 100)', required: false, example: 20)]
    #[ResponseFromApiResource(AuthorResource::class, Author::class, collection: true, paginate: 20)]
    public function index(): AnonymousResourceCollection
    {
        $perPage = (int) request()->input('per_page', config('news-aggregator.pagination.per_page', 20));
        $hasArticles = request()->boolean('has_articles', false);
        $page = request()->input('page', 1);

        $cacheKey = "authors:page:{$page}:per_page:{$perPage}:has_articles:{$hasArticles}";

        $authors = Cache::tags(['authors'])
            ->remember($cacheKey, 3600, function () use ($perPage, $hasArticles) {
                $query = Author::query()
                    ->withCount('articles')
                    ->orderBy('name');

                if ($hasArticles) {
                    $query->has('articles');
                }

                return $query->paginate($perPage);
            });

        return AuthorResource::collection($authors);
    }

    /**
     * Get a single author
     *
     * Retrieve detailed information about a specific author.
     */
    #[ResponseFromApiResource(AuthorResource::class, Author::class)]
    public function show(Author $author): AuthorResource
    {
        $cacheKey = "author:{$author->id}:details";

        $author = Cache::tags(['authors'])
            ->remember($cacheKey, 3600, function () use ($author) {
                $author->loadCount('articles');

                $author->load(['articles' => function ($query) {
                    $query->latest('published_at')->limit(10);
                }]);

                return $author;
            });

        return new AuthorResource($author);
    }
}
