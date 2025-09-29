<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Latest tech news and innovations'],
            ['name' => 'Business', 'slug' => 'business', 'description' => 'Business and financial news'],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports news and updates'],
            ['name' => 'Entertainment', 'slug' => 'entertainment', 'description' => 'Entertainment and celebrity news'],
            ['name' => 'Health', 'slug' => 'health', 'description' => 'Health and wellness news'],
            ['name' => 'Science', 'slug' => 'science', 'description' => 'Scientific discoveries and research'],
            ['name' => 'Politics', 'slug' => 'politics', 'description' => 'Political news and analysis'],
            ['name' => 'World', 'slug' => 'world', 'description' => 'International news'],
            ['name' => 'General', 'slug' => 'general', 'description' => 'General news'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
