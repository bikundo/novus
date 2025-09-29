<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'name'           => 'NewsAPI General',
                'slug'           => 'newsapi-general',
                'api_identifier' => 'newsapi',
                'description'    => 'General news aggregation from NewsAPI',
                'url'            => 'https://newsapi.org',
                'category'       => 'general',
                'language'       => 'en',
                'country'        => 'us',
                'is_active'      => true,
            ],
            [
                'name'           => 'The Guardian',
                'slug'           => 'the-guardian',
                'api_identifier' => 'the-guardian',
                'description'    => 'British daily newspaper',
                'url'            => 'https://www.theguardian.com',
                'category'       => 'general',
                'language'       => 'en',
                'country'        => 'gb',
                'is_active'      => true,
            ],
            [
                'name'           => 'The New York Times',
                'slug'           => 'new-york-times',
                'api_identifier' => 'nyt',
                'description'    => 'American newspaper based in New York City',
                'url'            => 'https://www.nytimes.com',
                'category'       => 'general',
                'language'       => 'en',
                'country'        => 'us',
                'is_active'      => true,
            ],
            [
                'name'           => 'BBC News',
                'slug'           => 'bbc-news',
                'api_identifier' => 'bbc-news',
                'description'    => 'British public service broadcaster',
                'url'            => 'https://www.bbc.com/news',
                'category'       => 'general',
                'language'       => 'en',
                'country'        => 'gb',
                'is_active'      => true,
            ],
        ];

        foreach ($sources as $source) {
            Source::create($source);
        }
    }
}
