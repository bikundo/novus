<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id'  => fake()->unique()->uuid(),
            'source_id'    => Source::factory(),
            'title'        => fake()->sentence(),
            'description'  => fake()->paragraph(),
            'content'      => fake()->paragraphs(5, true),
            'url'          => fake()->unique()->url(),
            'image_url'    => fake()->imageUrl(640, 480, 'news'),
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the article was published recently.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the article is old.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => fake()->dateTimeBetween('-1 year', '-6 months'),
        ]);
    }
}
