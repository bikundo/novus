<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Group;
use App\Services\PersonalizedFeedService;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Authenticated;
use App\Http\Resources\Api\V1\ArticleResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Personalized Feed', 'Personalized article feed based on user preferences')]
#[Authenticated]
class PersonalizedFeedController extends Controller
{
    public function __construct(
        protected readonly PersonalizedFeedService $feedService,
    ) {}

    /**
     * Get a personalized article feed
     *
     * Retrieve articles ranked by relevance to the authenticated user's preferences.
     * Articles are scored based on:
     * - Preferred sources (3 points)
     * - Preferred categories (2 points per match)
     * - Preferred authors (2.5 points per match)
     * - Recency (up to 5 points, decreasing with age)
     *
     * Results are cached for 30 minutes per user.
     */
    #[QueryParam('page', 'integer', 'Page number for pagination', required: false, example: 1)]
    #[QueryParam('per_page', 'integer', 'Number of items per page (max 100)', required: false, example: 20)]
    #[ResponseFromApiResource(ArticleResource::class, \App\Models\Article::class, collection: true, paginate: 20)]
    public function index(): AnonymousResourceCollection
    {
        $user = auth()->user();
        $perPage = (int) request()->input('per_page', config('news-aggregator.pagination.per_page', 20));

        $articles = $this->feedService->getPersonalizedFeed($user, $perPage);

        return ArticleResource::collection($articles);
    }
}
