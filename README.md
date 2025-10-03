# Novus - News Aggregator API

A powerful Laravel-based news aggregator that fetches, stores, and serves articles from multiple trusted news sources with full-text search, personalized feeds, and comprehensive analytics.

## Features

- **Multi-Source Aggregation** - Fetches articles from NewsAPI, The Guardian, and The New York Times
- **Full-Text Search** - Lightning-fast search powered by Typesense
- **Personalized Feeds** - Intelligent content ranking based on user preferences
- **Search Analytics** - Comprehensive tracking and insights on search behavior
- **RESTful API** - Well-documented endpoints with filtering and pagination (generated with Scribe)
- **Queue System** - Background job processing with Laravel Horizon
- **Redis Caching** - Optimized performance with intelligent caching strategies
- **Comprehensive Tests** - Full test coverage with Pest PHP

## Quick Start

### Prerequisites

- **Docker Desktop** - [Download here](https://www.docker.com/products/docker-desktop)
- **Git** - [Download here](https://git-scm.com/downloads)

That's all you need! Everything else runs in Docker containers.

### One-Command Setup

```bash
make setup
```

This will automatically set up Novus:
1. Check Docker is running
2. Create `.env` file from example
3. Create all storage directories with correct permissions
4. Start all Docker containers (MySQL, Redis, Typesense)
5. Wait for services to be healthy
6. Generate application encryption key
7. Run database migrations
8. Seed database with news sources
9. Build frontend assets
10. Fetch initial articles from news sources

**Setup time:** ~3-5 minutes

### Manual Setup (Alternative)

If you prefer manual control:

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

# 6. Fetch articles
./vendor/bin/sail artisan news:fetch
```

## Access the Application

Once setup is complete, access Novus at:

- **Application:** http://localhost:8080
- **API Documentation:** http://localhost:8080/docs
- **Horizon Dashboard:** http://localhost:8080/horizon

## Available Commands

```bash
make help        # Show all available commands
make setup       # Complete initial setup
make up          # Start all containers
make down        # Stop all containers
make restart     # Restart containers
make test        # Run the test suite
make pint        # Format code with Laravel Pint
make fetch       # Fetch fresh articles
make fresh       # Fresh database with seeds
make logs        # View container logs
make shell       # Access application shell
make clean       # Clean up containers and caches
make reset       # Complete reset and setup
```

## Daily Development

```bash
make up          # Start working
make fetch       # Fetch fresh articles
make test        # Run tests
make down        # Stop when done
```

## API Endpoints

All endpoints are prefixed with `/api/v1/`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/articles` | List all articles with filters |
| GET | `/articles/{id}` | Get single article details |
| GET | `/articles/search` | Search articles |
| GET | `/categories` | List all categories |
| GET | `/sources` | List all news sources |
| GET | `/authors` | List all authors |
| POST | `/preferences` | Save user preferences |
| GET | `/preferences` | Get user preferences |

See full API documentation at: http://localhost:8080/docs

## Configuration

### News API Keys

To fetch articles, you need API keys from:

1. **NewsAPI** - https://newsapi.org/register
2. **The Guardian** - https://open-platform.theguardian.com/access/
3. **New York Times** - https://developer.nytimes.com/get-started

Add them to your `.env` file:

```env
NEWSAPI_KEY=your_key_here
GUARDIAN_API_KEY=your_key_here
NYT_API_KEY=your_key_here
```

### Environment Variables

Key configuration in `.env`:

```env
APP_URL=http://localhost:8080
DB_DATABASE=novus
CACHE_STORE=redis
SCOUT_DRIVER=typesense
```

## Testing

```bash
make test                           # Run all tests
make test-coverage                  # Run with coverage
make test-filter name="article"     # Run specific tests
```

Or use Sail directly:

```bash
./vendor/bin/sail artisan test
./vendor/bin/sail artisan test --coverage
./vendor/bin/sail artisan test --filter=article
```

## Troubleshooting

### Containers won't start

```bash
docker info                    # Check Docker is running
make logs                      # View logs
make restart                   # Restart containers
```

### Database connection issues

```bash
./vendor/bin/sail exec mysql mysqladmin ping
make fresh                     # Re-run migrations
```

### Permission errors

```bash
chmod -R 777 storage bootstrap/cache
./vendor/bin/sail exec laravel.test chmod -R 777 storage bootstrap/cache
```

### Clear all caches

```bash
make cache-clear
```

## Tech Stack

- **Framework:** Laravel 12
- **Database:** MySQL 8.0
- **Cache:** Redis
- **Search:** Typesense
- **Queue:** Laravel Horizon
- **Testing:** Pest PHP
- **Code Quality:** Laravel Pint
- **API Documentation:** Scribe

## Project Structure

```
├── app/
│   ├── Http/Controllers/Api/V1/  # API Controllers
│   ├── Jobs/                      # Background Jobs
│   ├── Models/                    # Eloquent Models
│   ├── Services/                  # Business Logic
│   └── Observers/                 # Model Observers
├── database/
│   ├── migrations/                # Database Migrations
│   └── seeders/                   # Database Seeders
├── tests/
│   ├── Feature/                   # Feature Tests
│   └── Unit/                      # Unit Tests
└── routes/
    └── api.php                    # API Routes
```
