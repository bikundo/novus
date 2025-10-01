<?php

declare(strict_types=1);

use App\Models\Source;
use App\Models\Article;

use function Pest\Laravel\getJson;

it('returns paginated list of sources', function () {
    Source::factory()->count(15)->create();

    $response = getJson(route('api.v1.sources.index'));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'url',
                    'articles_count',
                ],
            ],
            'links',
            'meta',
        ]);
});

it('includes articles count for each source', function () {
    $source = Source::factory()->create();
    Article::factory()->count(5)->create(['source_id' => $source->id]);

    $response = getJson(route('api.v1.sources.index'));

    $response->assertSuccessful()
        ->assertJsonPath('data.0.articles_count', 5);
});

it('returns a single source with articles', function () {
    $source = Source::factory()->create();
    Article::factory()->count(3)->create(['source_id' => $source->id]);

    $response = getJson(route('api.v1.sources.show', $source));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
                'url',
                'articles_count',
                'articles' => [
                    '*' => ['id', 'title', 'url'],
                ],
            ],
        ])
        ->assertJsonCount(3, 'data.articles');
});

it('returns 404 for non-existent source', function () {
    $response = getJson(route('api.v1.sources.show', 99999));

    $response->assertNotFound();
});

it('orders sources alphabetically by name', function () {
    Source::factory()->create(['name' => 'Zebra News']);
    Source::factory()->create(['name' => 'Alpha News']);
    Source::factory()->create(['name' => 'Beta News']);

    $response = getJson(route('api.v1.sources.index'));

    $response->assertSuccessful();

    $sources = $response->json('data');
    expect($sources[0]['name'])->toBe('Alpha News');
    expect($sources[1]['name'])->toBe('Beta News');
    expect($sources[2]['name'])->toBe('Zebra News');
});

it('respects per_page parameter for sources', function () {
    Source::factory()->count(20)->create();

    $response = getJson(route('api.v1.sources.index', ['per_page' => 5]));

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data');
});
