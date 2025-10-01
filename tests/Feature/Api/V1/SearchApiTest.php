<?php

declare(strict_types=1);

use App\Models\Source;
use App\Models\Article;
use App\Models\Category;

use function Pest\Laravel\getJson;

beforeEach(function () {
    Article::makeAllSearchable();
});

it('searches articles by query', function () {
    Article::factory()->create(['title' => 'Laravel Framework Best Practices']);
    Article::factory()->create(['title' => 'PHP Development Tips']);
    Article::factory()->create(['description' => 'Learn Laravel today']);

    $response = getJson(route('api.v1.search', ['q' => 'Laravel']));

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('returns empty results for no matches', function () {
    Article::factory()->count(5)->create();

    $response = getJson(route('api.v1.search', ['q' => 'nonexistentquery12345']));

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

it('searches with date range filters', function () {
    Article::factory()->create([
        'title'        => 'Old Laravel Article',
        'published_at' => now()->subDays(10),
    ]);
    Article::factory()->create([
        'title'        => 'Recent Laravel Article',
        'published_at' => now()->subDays(2),
    ]);

    $response = getJson(route('api.v1.search', [
        'q'    => 'Laravel',
        'from' => now()->subDays(5)->format('Y-m-d'),
        'to'   => now()->format('Y-m-d'),
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('respects per_page in search results', function () {
    Article::factory()->count(20)->create(['title' => 'Laravel Article']);

    $response = getJson(route('api.v1.search', ['q' => 'Laravel', 'per_page' => 5]));

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data');
});

it('validates search query length', function () {
    $response = getJson(route('api.v1.search', ['q' => str_repeat('a', 300)]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors('q');
});

it('validates search filters', function () {
    $response = getJson(route('api.v1.search', [
        'from' => 'invalid-date',
        'to'   => 'also-invalid',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['from', 'to']);
});

it('searches in title, description, and content', function () {
    Article::factory()->create(['title' => 'PHP Framework']);
    Article::factory()->create(['description' => 'Learn PHP programming']);
    Article::factory()->create(['content' => 'PHP is a popular language']);

    $response = getJson(route('api.v1.search', ['q' => 'PHP']));

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});
