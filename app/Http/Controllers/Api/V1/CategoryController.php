<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Group;
use App\Services\Cache\ArticleCacheService;
use App\Http\Resources\Api\V1\CategoryResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

#[Group('Categories', 'Endpoints for managing article categories')]
class CategoryController extends Controller
{
    public function __construct(
        protected readonly ArticleCacheService $cacheService,
    ) {}

    /**
     * List all categories
     *
     * Retrieve a list of all available article categories in the system.
     * Results are cached for 24 hours.
     */
    #[ResponseFromApiResource(CategoryResource::class, Category::class, collection: true)]
    public function index(): AnonymousResourceCollection
    {
        $perPage = (int) request()->input('per_page', config('news-aggregator.pagination.per_page', 20));
        $hasArticles = request()->boolean('has_articles', false);
        $page = request()->input('page', 1);

        $cacheKey = "categories:page:{$page}:per_page:{$perPage}:has_articles:{$hasArticles}";

        $categories = Cache::tags(['categories'])
            ->remember($cacheKey, 86400, function () use ($perPage, $hasArticles) {
                $query = Category::query()
                    ->withCount('articles')
                    ->orderBy('name');

                if ($hasArticles) {
                    $query->has('articles');
                }

                return $query->paginate($perPage);
            });

        return CategoryResource::collection($categories);
    }

    /**
     * Get a single category
     *
     * Retrieve detailed information about a specific category.
     */
    #[ResponseFromApiResource(CategoryResource::class, Category::class)]
    public function show(Category $category): CategoryResource
    {
        $cacheKey = "category:{$category->id}:details";

        $category = Cache::tags(['categories'])
            ->remember($cacheKey, 86400, function () use ($category) {
                $category->loadCount('articles');

                $category->load(['articles' => function ($query) {
                    $query->latest('published_at')->limit(10);
                }]);

                return $category;
            });

        return new CategoryResource($category);
    }
}
