<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PersonalizedFeedService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get personalized article feed for a user based on their preferences.
     */
    public function getPersonalizedFeed(User $user, int $perPage = 20): LengthAwarePaginator
    {
        $cacheKey = "personalized_feed:user_{$user->id}:page_" . request()->input('page', 1) . ":per_page_{$perPage}";

        return Cache::tags(['personalized_feeds', "user_{$user->id}"])
            ->remember($cacheKey, 1800, function () use ($user, $perPage) {
                return $this->buildPersonalizedQuery($user, $perPage);
            });
    }

    /**
     * Build the personalized query based on user preferences.
     */
    protected function buildPersonalizedQuery(User $user, int $perPage): LengthAwarePaginator
    {
        $preference = $user->preference;

        $query = Article::query()
            ->with(['source', 'categories', 'authors'])
            ->latest('published_at');

        if (!$preference) {
            return $query->paginate($perPage);
        }

        $hasPreferences = false;
        $scoredArticles = collect();

        if (!empty($preference->preferred_sources) ||
            !empty($preference->preferred_categories) ||
            !empty($preference->preferred_authors)) {
            $hasPreferences = true;

            $allArticles = Article::query()
                ->with(['source', 'categories', 'authors'])
                ->where('published_at', '>=', now()->subDays(30))
                ->get();

            $scoredArticles = $allArticles->map(function ($article) use ($preference) {
                $article->relevance_score = $this->calculateRelevanceScore($article, $preference);

                return $article;
            })->sortByDesc('relevance_score');
        }

        if ($hasPreferences && $scoredArticles->isNotEmpty()) {
            $page = request()->input('page', 1);
            $offset = ($page - 1) * $perPage;

            $paginatedItems = $scoredArticles->slice($offset, $perPage)->values();

            return new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedItems,
                $scoredArticles->count(),
                $perPage,
                $page,
                ['path' => request()->url()]
            );
        }

        return $query->paginate($perPage);
    }

    /**
     * Calculate relevance score for an article based on user preferences.
     */
    protected function calculateRelevanceScore(Article $article, $preference): float
    {
        $score = 0.0;

        if (!empty($preference->preferred_sources)) {
            if (in_array($article->source->slug, $preference->preferred_sources)) {
                $score += 3.0;
            }
        }

        if (!empty($preference->preferred_categories)) {
            $articleCategorySlugs = $article->categories->pluck('slug')->toArray();
            $matchingCategories = array_intersect($preference->preferred_categories, $articleCategorySlugs);
            $score += count($matchingCategories) * 2.0;
        }

        if (!empty($preference->preferred_authors)) {
            $articleAuthorNames = $article->authors->pluck('name')->toArray();
            $matchingAuthors = array_intersect($preference->preferred_authors, $articleAuthorNames);
            $score += count($matchingAuthors) * 2.5;
        }

        $daysSincePublished = now()->diffInDays($article->published_at);
        $recencyScore = max(0, 5 - ($daysSincePublished * 0.5));
        $score += $recencyScore;

        return $score;
    }

    /**
     * Invalidate personalized feed cache for a user.
     */
    public function invalidateUserFeed(int $userId): void
    {
        Cache::tags(["user_{$userId}"])->flush();
    }

    /**
     * Invalidate all personalized feeds.
     */
    public function invalidateAllFeeds(): void
    {
        Cache::tags(['personalized_feeds'])->flush();
    }
}
