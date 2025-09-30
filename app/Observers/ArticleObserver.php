<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Article;
use App\Services\Cache\ArticleCacheService;

class ArticleObserver
{
    public function __construct(
        protected readonly ArticleCacheService $cacheService,
    ) {}

    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        $this->cacheService->invalidateArticles();
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        $this->cacheService->invalidateArticles();
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        $this->cacheService->invalidateArticles();
    }

    /**
     * Handle the Article "restored" event.
     */
    public function restored(Article $article): void
    {
        $this->cacheService->invalidateArticles();
    }

    /**
     * Handle the Article "force deleted" event.
     */
    public function forceDeleted(Article $article): void
    {
        $this->cacheService->invalidateArticles();
    }
}
