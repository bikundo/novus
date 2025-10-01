<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;

use function Pest\Laravel\getJson;

it('returns paginated list of categories', function () {
    Category::factory()->count(15)->create();

    $response = getJson(route('api.v1.categories.index'));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'articles_count',
                ],
            ],
            'links',
            'meta',
        ]);
});

it('includes articles count for each category', function () {
    $category = Category::factory()->create();
    $articles = Article::factory()->count(7)->create();
    foreach ($articles as $article) {
        $article->categories()->attach($category);
    }

    $response = getJson(route('api.v1.categories.index'));

    $response->assertSuccessful()
        ->assertJsonPath('data.0.articles_count', 7);
});

it('returns a single category with articles', function () {
    $category = Category::factory()->create();
    $articles = Article::factory()->count(4)->create();
    foreach ($articles as $article) {
        $article->categories()->attach($category);
    }

    $response = getJson(route('api.v1.categories.show', $category));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
                'articles_count',
                'articles' => [
                    '*' => ['id', 'title', 'url'],
                ],
            ],
        ])
        ->assertJsonCount(4, 'data.articles');
});

it('returns 404 for non-existent category', function () {
    $response = getJson(route('api.v1.categories.show', 99999));

    $response->assertNotFound();
});

it('orders categories alphabetically by name', function () {
    Category::factory()->create(['name' => 'Technology']);
    Category::factory()->create(['name' => 'Business']);
    Category::factory()->create(['name' => 'Sports']);

    $response = getJson(route('api.v1.categories.index'));

    $response->assertSuccessful();

    $categories = $response->json('data');
    expect($categories[0]['name'])->toBe('Business');
    expect($categories[1]['name'])->toBe('Sports');
    expect($categories[2]['name'])->toBe('Technology');
});

it('respects per_page parameter for categories', function () {
    Category::factory()->count(25)->create();

    $response = getJson(route('api.v1.categories.index', ['per_page' => 10]));

    $response->assertSuccessful()
        ->assertJsonCount(10, 'data');
});

it('excludes categories with no articles when filter applied', function () {
    $categoryWithArticles = Category::factory()->create();
    $articles = Article::factory()->count(2)->create();
    foreach ($articles as $article) {
        $article->categories()->attach($categoryWithArticles);
    }

    Category::factory()->count(3)->create();

    $response = getJson(route('api.v1.categories.index', ['has_articles' => true]));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});
