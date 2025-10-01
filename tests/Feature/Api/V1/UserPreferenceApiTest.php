<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserPreference;

use function Pest\Laravel\getJson;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('returns user preferences when authenticated', function () {
    $preference = UserPreference::factory()->create([
        'user_id'              => $this->user->id,
        'preferred_sources'    => ['source-1', 'source-2'],
        'preferred_categories' => ['tech', 'business'],
        'preferred_authors'    => ['John Doe'],
    ]);

    $response = actingAs($this->user)->getJson(route('api.v1.preferences.show'));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id',
                'preferred_sources',
                'preferred_categories',
                'preferred_authors',
            ],
        ])
        ->assertJson([
            'data' => [
                'preferred_sources'    => ['source-1', 'source-2'],
                'preferred_categories' => ['tech', 'business'],
                'preferred_authors'    => ['John Doe'],
            ],
        ]);
});

it('returns empty preferences when none exist', function () {
    $response = actingAs($this->user)->getJson(route('api.v1.preferences.show'));

    $response->assertSuccessful()
        ->assertJson([
            'data' => [
                'preferred_sources'    => [],
                'preferred_categories' => [],
                'preferred_authors'    => [],
            ],
        ]);
});

it('requires authentication to view preferences', function () {
    $response = getJson(route('api.v1.preferences.show'));

    $response->assertUnauthorized();
});

it('creates new user preferences', function () {
    $response = actingAs($this->user)->postJson(route('api.v1.preferences.store'), [
        'preferred_sources'    => ['the-guardian', 'nyt'],
        'preferred_categories' => ['technology', 'science'],
        'preferred_authors'    => ['Jane Smith', 'Bob Johnson'],
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'data' => [
                'preferred_sources'    => ['the-guardian', 'nyt'],
                'preferred_categories' => ['technology', 'science'],
                'preferred_authors'    => ['Jane Smith', 'Bob Johnson'],
            ],
        ]);

    expect(UserPreference::where('user_id', $this->user->id)->exists())->toBeTrue();
});

it('updates existing user preferences', function () {
    UserPreference::factory()->create([
        'user_id'              => $this->user->id,
        'preferred_sources'    => ['old-source'],
        'preferred_categories' => ['old-category'],
        'preferred_authors'    => [],
    ]);

    $response = actingAs($this->user)->postJson(route('api.v1.preferences.store'), [
        'preferred_sources'    => ['new-source'],
        'preferred_categories' => ['new-category'],
        'preferred_authors'    => ['New Author'],
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'data' => [
                'preferred_sources'    => ['new-source'],
                'preferred_categories' => ['new-category'],
                'preferred_authors'    => ['New Author'],
            ],
        ]);

    expect(UserPreference::where('user_id', $this->user->id)->count())->toBe(1);
});

it('requires authentication to store preferences', function () {
    $response = postJson(route('api.v1.preferences.store'), [
        'preferred_sources' => ['test'],
    ]);

    $response->assertUnauthorized();
});

it('validates preference data types', function () {
    $response = actingAs($this->user)->postJson(route('api.v1.preferences.store'), [
        'preferred_sources'    => 'not-an-array',
        'preferred_categories' => ['valid'],
        'preferred_authors'    => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['preferred_sources', 'preferred_authors']);
});

it('validates each preference array element is a string', function () {
    $response = actingAs($this->user)->postJson(route('api.v1.preferences.store'), [
        'preferred_sources'    => ['valid-source', 123, true],
        'preferred_categories' => ['tech'],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['preferred_sources.1', 'preferred_sources.2']);
});

it('allows empty arrays for preferences', function () {
    $response = actingAs($this->user)->postJson(route('api.v1.preferences.store'), [
        'preferred_sources'    => [],
        'preferred_categories' => [],
        'preferred_authors'    => [],
    ]);

    $response->assertSuccessful();
});

it('validates maximum number of preferences', function () {
    $tooManySources = array_fill(0, 51, 'source');

    $response = actingAs($this->user)->postJson(route('api.v1.preferences.store'), [
        'preferred_sources'    => $tooManySources,
        'preferred_categories' => [],
        'preferred_authors'    => [],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('preferred_sources');
});
