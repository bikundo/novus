<?php

declare(strict_types=1);

return [
    'providers' => [
        'newsapi' => [
            'enabled'    => env('NEWSAPI_ENABLED', true),
            'api_key'    => env('NEWSAPI_KEY'),
            'base_url'   => 'https://newsapi.org/v2',
            'rate_limit' => 1000,
        ],
        'guardian' => [
            'enabled'    => env('GUARDIAN_ENABLED', true),
            'api_key'    => env('GUARDIAN_API_KEY'),
            'base_url'   => 'https://content.guardianapis.com',
            'rate_limit' => 5000,
        ],
        'nyt' => [
            'enabled'    => env('NYT_ENABLED', true),
            'api_key'    => env('NYT_API_KEY'),
            'base_url'   => 'https://api.nytimes.com/svc',
            'rate_limit' => 4000,
        ],
        'bbc' => [
            'enabled'    => env('BBC_ENABLED', false),
            'api_key'    => env('BBC_API_KEY'),
            'base_url'   => 'https://bbc.com/api',
            'rate_limit' => 1000,
        ],
    ],

    'fetch_interval' => env('NEWS_FETCH_INTERVAL', 60),
    'retention_days' => env('NEWS_RETENTION_DAYS', 90),
    'per_page'       => env('NEWS_PER_PAGE', 20),
    'max_results'    => env('NEWS_MAX_RESULTS', 100),

    'cache' => [
        'ttl_articles'   => env('CACHE_TTL_ARTICLES', 900),
        'ttl_sources'    => env('CACHE_TTL_SOURCES', 86400),
        'ttl_categories' => env('CACHE_TTL_CATEGORIES', 86400),
        'ttl_search'     => env('CACHE_TTL_SEARCH', 1800),
    ],
];
