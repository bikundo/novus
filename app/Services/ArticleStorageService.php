<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use App\Models\Author;
use App\Models\Source;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArticleStorageService
{
    /**
     * Store normalized articles in the database.
     *
     * @param  array<int, array<string, mixed>>  $normalizedArticles
     * @return int Number of articles stored
     */
    public function storeArticles(array $normalizedArticles): int
    {
        $storedCount = 0;

        foreach ($normalizedArticles as $articleData) {
            if ($this->storeArticle($articleData)) {
                $storedCount++;
            }
        }

        return $storedCount;
    }

    /**
     * Store a single normalized article.
     *
     * @param  array<string, mixed>  $articleData
     */
    public function storeArticle(array $articleData): bool
    {
        if (!$this->isValidArticleData($articleData)) {
            Log::warning('Invalid article data', ['data' => $articleData]);

            return false;
        }

        try {
            DB::beginTransaction();

            $source = $this->findOrCreateSource($articleData['source']);

            $article = $this->createOrUpdateArticle($articleData, $source->id);

            if (!empty($articleData['category'])) {
                $this->attachCategories($article, $articleData['category']);
            }

            if (!empty($articleData['author'])) {
                $this->attachAuthors($article, $articleData['author']);
            }

            DB::commit();

            return true;
        }
        catch (Exception $exception) {
            DB::rollBack();

            Log::error('Failed to store article', [
                'error'   => $exception->getMessage(),
                'article' => $articleData,
            ]);

            return false;
        }
    }

    /**
     * Validate article data before storage.
     *
     * @param  array<string, mixed>  $articleData
     */
    private function isValidArticleData(array $articleData): bool
    {
        return !empty($articleData['external_id'])
            && !empty($articleData['title'])
            && !empty($articleData['url'])
            && !empty($articleData['published_at']);
    }

    /**
     * Find or create a source.
     */
    private function findOrCreateSource(string $sourceName): Source
    {
        $slug = Str::slug($sourceName);

        return Source::where('slug', $slug)
            ->orWhere('name', $sourceName)
            ->firstOr(function () use ($sourceName, $slug) {
                return Source::create([
                    'name'           => $sourceName,
                    'slug'           => $slug,
                    'api_identifier' => $slug,
                    'is_active'      => true,
                ]);
            });
    }

    /**
     * Create or update an article.
     *
     * @param  array<string, mixed>  $articleData
     */
    private function createOrUpdateArticle(array $articleData, int $sourceId): Article
    {
        return Article::updateOrCreate(
            ['external_id' => $articleData['external_id']],
            [
                'source_id'    => $sourceId,
                'title'        => $articleData['title'],
                'description'  => $articleData['description'],
                'content'      => $articleData['content'],
                'url'          => $articleData['url'],
                'image_url'    => $articleData['image_url'],
                'published_at' => $articleData['published_at'],
            ]
        );
    }

    /**
     * Attach categories to an article.
     *
     * @param  array<string>  $categoryNames
     */
    private function attachCategories(Article $article, array $categoryNames): void
    {
        $categoryIds = [];

        foreach ($categoryNames as $categoryName) {
            $category = $this->findOrCreateCategory($categoryName);
            $categoryIds[] = $category->id;
        }

        $article->categories()->sync($categoryIds);
    }

    /**
     * Find or create a category.
     */
    private function findOrCreateCategory(string $categoryName): Category
    {
        $slug = Str::slug($categoryName);

        return Category::firstOrCreate(
            ['slug' => $slug],
            ['name' => ucfirst($categoryName)]
        );
    }

    /**
     * Attach authors to an article.
     *
     * @param  array<string>  $authorNames
     */
    private function attachAuthors(Article $article, array $authorNames): void
    {
        $authorIds = [];

        foreach ($authorNames as $authorName) {
            if (empty($authorName)) {
                continue;
            }

            $author = $this->findOrCreateAuthor($authorName);
            $authorIds[] = $author->id;
        }

        $article->authors()->sync($authorIds);
    }

    /**
     * Find or create an author.
     */
    private function findOrCreateAuthor(string $authorName): Author
    {
        $slug = Str::slug($authorName);

        return Author::firstOrCreate(
            ['slug' => $slug],
            ['name' => $authorName]
        );
    }
}
