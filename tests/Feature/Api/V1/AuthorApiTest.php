<?php

declare(strict_types=1);

use App\Models\Author;
use App\Models\Article;

use function Pest\Laravel\getJson;

it('returns paginated list of authors', function () {
    Author::factory()->count(15)->create();

    $response = getJson(route('api.v1.authors.index'));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'articles_count',
                ],
            ],
            'links',
            'meta',
        ]);
});

it('includes articles count for each author', function () {
    $author = Author::factory()->create();
    $articles = Article::factory()->count(6)->create();
    foreach ($articles as $article) {
        $article->authors()->attach($author);
    }

    $response = getJson(route('api.v1.authors.index'));

    $response->assertSuccessful()
        ->assertJsonPath('data.0.articles_count', 6);
});

it('returns a single author with articles', function () {
    $author = Author::factory()->create();
    $articles = Article::factory()->count(3)->create();
    foreach ($articles as $article) {
        $article->authors()->attach($author);
    }

    $response = getJson(route('api.v1.authors.show', $author));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'articles_count',
                'articles' => [
                    '*' => ['id', 'title', 'url'],
                ],
            ],
        ])
        ->assertJsonCount(3, 'data.articles');
});

it('returns 404 for non-existent author', function () {
    $response = getJson(route('api.v1.authors.show', 99999));

    $response->assertNotFound();
});

it('orders authors alphabetically by name', function () {
    Author::factory()->create(['name' => 'Zoe Smith']);
    Author::factory()->create(['name' => 'Alice Johnson']);
    Author::factory()->create(['name' => 'Bob Williams']);

    $response = getJson(route('api.v1.authors.index'));

    $response->assertSuccessful();

    $authors = $response->json('data');
    expect($authors[0]['name'])->toBe('Alice Johnson');
    expect($authors[1]['name'])->toBe('Bob Williams');
    expect($authors[2]['name'])->toBe('Zoe Smith');
});

it('respects per_page parameter for authors', function () {
    Author::factory()->count(30)->create();

    $response = getJson(route('api.v1.authors.index', ['per_page' => 8]));

    $response->assertSuccessful()
        ->assertJsonCount(8, 'data');
});

it('excludes authors with no articles when filter applied', function () {
    $authorWithArticles = Author::factory()->create();
    $articles = Article::factory()->count(2)->create();
    foreach ($articles as $article) {
        $article->authors()->attach($authorWithArticles);
    }

    Author::factory()->count(4)->create();

    $response = getJson(route('api.v1.authors.index', ['has_articles' => true]));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});
