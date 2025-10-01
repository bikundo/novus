<?php

declare(strict_types=1);

use App\Models\Author;
use App\Models\Source;
use App\Models\Article;
use App\Models\Category;

use function Pest\Laravel\getJson;

it('returns paginated list of articles', function () {
    Article::factory()->count(30)->create();

    $response = getJson(route('api.v1.articles.index'));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'content',
                    'url',
                    'image_url',
                    'published_at',
                    'source',
                    'categories',
                    'authors',
                ],
            ],
            'links',
            'meta',
        ]);
});

it('filters articles by source', function () {
    $source = Source::factory()->create(['slug' => 'test-source']);
    Article::factory()->count(5)->create(['source_id' => $source->id]);
    Article::factory()->count(3)->create();

    $response = getJson(route('api.v1.articles.index', ['source' => 'test-source']));

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data');
});

it('filters articles by category', function () {
    $category = Category::factory()->create(['slug' => 'technology']);
    $articles = Article::factory()->count(3)->create();
    foreach ($articles as $article) {
        $article->categories()->attach($category);
    }
    Article::factory()->count(2)->create();

    $response = getJson(route('api.v1.articles.index', ['category' => 'technology']));

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('filters articles by author', function () {
    $author = Author::factory()->create(['name' => 'John Doe']);
    $articles = Article::factory()->count(4)->create();
    foreach ($articles as $article) {
        $article->authors()->attach($author);
    }
    Article::factory()->count(2)->create();

    $response = getJson(route('api.v1.articles.index', ['author' => 'John Doe']));

    $response->assertSuccessful()
        ->assertJsonCount(4, 'data');
});

it('filters articles by date range', function () {
    Article::factory()->create(['published_at' => now()->subDays(10)]);
    Article::factory()->count(3)->create(['published_at' => now()->subDays(5)]);
    Article::factory()->create(['published_at' => now()->subDays(1)]);

    $response = getJson(route('api.v1.articles.index', [
        'from' => now()->subDays(7)->format('Y-m-d'),
        'to'   => now()->subDays(2)->format('Y-m-d'),
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('respects per_page parameter', function () {
    Article::factory()->count(50)->create();

    $response = getJson(route('api.v1.articles.index', ['per_page' => 5]));

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data');
});

it('returns a single article', function () {
    $article = Article::factory()
        ->has(Category::factory()->count(2))
        ->has(Author::factory()->count(1))
        ->create();

    $response = getJson(route('api.v1.articles.show', $article));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'content',
                'url',
                'image_url',
                'published_at',
                'source',
                'categories',
                'authors',
            ],
        ])
        ->assertJson([
            'data' => [
                'id'    => $article->id,
                'title' => $article->title,
            ],
        ]);
});

it('returns 404 for non-existent article', function () {
    $response = getJson(route('api.v1.articles.show', 99999));

    $response->assertNotFound();
});

it('validates date range filters', function () {
    $response = getJson(route('api.v1.articles.index', [
        'from' => now()->format('Y-m-d'),
        'to'   => now()->subDays(5)->format('Y-m-d'),
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors('to');
});

it('validates per_page maximum', function () {
    $response = getJson(route('api.v1.articles.index', ['per_page' => 150]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors('per_page');
});
