# News Aggregator - Quick Setup Guide

## Prerequisites

Before you begin, ensure you have the following installed on your machine:

- **Docker Desktop** (https://www.docker.com/products/docker-desktop)
- **Git**

That's it! Everything else runs inside Docker containers.

## One-Command Setup

For first-time setup, simply run:

```bash
./setup.sh
```

This automated script will:
1. Create your `.env` file from `.env.example`
2. Start all Docker containers (MySQL, Redis, Typesense)
3. Generate an application key
4. Run database migrations
5. Seed the database with initial data
6. Set up Typesense search indexes
7. Install NPM dependencies
8. Build frontend assets

## Manual Setup (Alternative)

If you prefer to set up manually:

```bash
# 1. Copy environment file
cp .env.example .env

# 2. Start Docker containers
./vendor/bin/sail up -d

# 3. Generate application key
./vendor/bin/sail artisan key:generate

# 4. Run migrations
./vendor/bin/sail artisan migrate

# 5. Seed database
./vendor/bin/sail artisan db:seed

# 6. Set up search indexes
./vendor/bin/sail artisan scout:sync-index-settings
./vendor/bin/sail artisan scout:import "App\Models\Article"

# 7. Install and build frontend
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

## Configure API Keys

Before fetching articles, add your API keys to the `.env` file:

```env
NEWSAPI_KEY=your_newsapi_key_here
GUARDIAN_API_KEY=your_guardian_key_here
NYT_API_KEY=your_nyt_key_here
```

Get your API keys from:
- NewsAPI: https://newsapi.org/register
- The Guardian: https://open-platform.theguardian.com/access/
- New York Times: https://developer.nytimes.com/get-started

## Access the Application

Once setup is complete:

- **Application**: http://localhost
- **API Documentation**: http://localhost:8080/docs
- **Horizon Dashboard**: http://localhost:8080/horizon (job queue monitoring)
- **Typesense Admin**: http://localhost:8108

## Fetch Your First Articles

```bash
# Fetch articles from all sources
./vendor/bin/sail artisan news:fetch

# Fetch from specific provider
./vendor/bin/sail artisan news:fetch --provider=newsapi
./vendor/bin/sail artisan news:fetch --provider=guardian
./vendor/bin/sail artisan news:fetch --provider=nyt
```

## Daily Commands

```bash
# Start containers
./vendor/bin/sail up

# Start in background
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# View logs
./vendor/bin/sail logs

# Run tests
./vendor/bin/sail test

# Access container shell
./vendor/bin/sail shell

# Run artisan commands
./vendor/bin/sail artisan [command]

# Clear cache
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
```

## Running Jobs

The application uses Laravel Horizon for queue management:

```bash
# Start Horizon (processes jobs)
./vendor/bin/sail artisan horizon

# Or access the dashboard at http://localhost:8080/horizon
```

## Testing

```bash
# Run all tests
./vendor/bin/sail test

# Run specific test file
./vendor/bin/sail test tests/Feature/Api/V1/ArticleApiTest.php

# Run with coverage
./vendor/bin/sail test --coverage
```

## Code Formatting

```bash
# Format code with Laravel Pint
./vendor/bin/sail pint

# Check formatting without changes
./vendor/bin/sail pint --test
```

## Troubleshooting

### Services not starting?
```bash
./vendor/bin/sail down
./vendor/bin/sail up -d --force-recreate
```

### Database connection issues?
Check that MySQL is healthy:
```bash
docker ps
```
Look for "healthy" status on the mysql container.

### Clear everything and start fresh?
```bash
./vendor/bin/sail down -v  # Removes volumes (deletes data!)
./setup.sh  # Run setup again
```

### Typesense not working?
Re-import the search indexes:
```bash
./vendor/bin/sail artisan scout:flush "App\Models\Article"
./vendor/bin/sail artisan scout:import "App\Models\Article"
```

## Development Workflow

1. Start containers: `./vendor/bin/sail up -d`
2. Make your changes
3. Run tests: `./vendor/bin/sail test`
4. Format code: `./vendor/bin/sail pint`
5. Commit your changes

## Project Structure

```
app/
├── Console/Commands/      # Artisan commands (news:fetch)
├── Http/
│   ├── Controllers/       # API controllers
│   ├── Requests/          # Form validation
│   └── Resources/         # API resources
├── Jobs/                  # Queue jobs
├── Models/                # Eloquent models
├── Services/              # Business logic
│   ├── NewsAggregator/    # News provider services
│   └── Cache/             # Caching services
database/
├── migrations/            # Database schema
├── seeders/               # Database seeders
└── factories/             # Model factories
tests/
├── Feature/               # Integration tests
└── Unit/                  # Unit tests
```

## Available API Endpoints

See full documentation at http://localhost:8080/docs

**Public Endpoints:**
- `GET /api/v1/articles` - List articles (with filters)
- `GET /api/v1/articles/{id}` - Single article
- `GET /api/v1/search` - Full-text search
- `GET /api/v1/sources` - List news sources
- `GET /api/v1/categories` - List categories
- `GET /api/v1/authors` - List authors

**Authenticated Endpoints:**
- `GET /api/v1/feed` - Personalized article feed
- `GET /api/v1/preferences` - User preferences
- `POST /api/v1/preferences` - Update preferences

## Support

For issues or questions, check:
- API Documentation: http://localhost:8080/docs
- Laravel Documentation: https://laravel.com/docs
- Project README: README.md

## Next Steps

1. Explore the API documentation at `/docs`
2. Try fetching articles: `./vendor/bin/sail artisan news:fetch`
3. Test the search: http://localhost:8080/api/v1/search?q=technology
4. Check Horizon dashboard: http://localhost:8080/horizon
5. Run the test suite: `./vendor/bin/sail test`

Happy coding! 🚀

