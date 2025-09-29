<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiLog>
 */
class ApiLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'api_provider'     => fake()->randomElement(['newsapi', 'guardian', 'nyt', 'bbc']),
            'endpoint'         => fake()->randomElement(['/everything', '/top-headlines', '/search', '/articles']),
            'status_code'      => fake()->randomElement([200, 200, 200, 400, 429, 500]),
            'response_time'    => fake()->numberBetween(100, 2000),
            'articles_fetched' => fake()->numberBetween(0, 100),
            'error_message'    => fake()->optional(0.2)->sentence(),
        ];
    }

    /**
     * Indicate that the API call was successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_code'      => 200,
            'error_message'    => null,
            'articles_fetched' => fake()->numberBetween(10, 100),
        ]);
    }

    /**
     * Indicate that the API call failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_code'      => fake()->randomElement([400, 401, 403, 429, 500, 503]),
            'error_message'    => fake()->sentence(),
            'articles_fetched' => 0,
        ]);
    }
}
