<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Source>
 */
class SourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name'           => $name,
            'slug'           => Str::slug($name),
            'api_identifier' => Str::slug($name),
            'description'    => fake()->sentence(),
            'url'            => fake()->url(),
            'category'       => fake()->randomElement(['technology', 'business', 'sports', 'entertainment', 'general']),
            'language'       => 'en',
            'country'        => fake()->countryCode(),
            'is_active'      => true,
        ];
    }

    /**
     * Indicate that the source is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
