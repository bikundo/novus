# News Aggregator API

A powerful Laravel-based news aggregator that fetches, stores, and serves articles from multiple trusted news sources with full-text search, personalized feeds, and comprehensive analytics.

## Features

- **Multi-Source Aggregation** - Fetches articles from NewsAPI, The Guardian, and The New York Times
- **Full-Text Search** - Lightning-fast search powered by Typesense with sub-100ms response times
- **Personalized Feeds** - Intelligent content ranking based on user preferences
- **Search Analytics** - Comprehensive tracking and insights on search behavior
- **RESTful API** - 11 well-documented endpoints with filtering and pagination
- **Queue System** - Background job processing with Laravel Horizon
- **Redis Caching** - Optimized performance with intelligent caching strategies
- **Comprehensive Tests** - 49 tests with 100% pass rate

## Quick Start

### Prerequisites

- Docker Desktop
- Git

That's all you need! Everything else runs in Docker containers.

### One-Command Setup

```bash
./setup.sh
```

This will set up everything automatically. See [SETUP.md](SETUP.md) for detailed instructions.

### Using Make Commands

```bash
make help        # Show all available commands
make setup       # Initial setup (run once)
make up          # Start containers
make test        # Run tests
make fetch       # Fetch articles from news sources
```

## Quick Commands

```bash
# Start the application
./vendor/bin/sail up -d

# Run tests
./vendor/bin/sail test

# Fetch articles
./vendor/bin/sail artisan news:fetch

# Access API documentation
open http://localhost/docs
```

## API Documentation

Once running, visit:
- **API Docs**: http://localhost/docs
- **Horizon Dashboard**: http://localhost/horizon
- **Application**: http://localhost

## Configuration

Add your API keys to `.env`:

```env
NEWSAPI_KEY=your_newsapi_key_here
GUARDIAN_API_KEY=your_guardian_key_here
NYT_API_KEY=your_nyt_key_here
```

Get API keys from:
- [NewsAPI](https://newsapi.org/register)
- [The Guardian](https://open-platform.theguardian.com/access/)
- [New York Times](https://developer.nytimes.com/get-started)

## Technology Stack

- **Backend**: Laravel 12 (PHP 8.4)
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Search**: Typesense
- **Queue**: Laravel Horizon
- **Testing**: Pest PHP
- **Containerization**: Docker (Laravel Sail)

## Architecture

- Service-oriented architecture with clear separation of concerns
- Repository pattern for complex queries
- Job batching for parallel news fetching
- Observer pattern for automatic search indexing
- Redis caching with tagged cache invalidation
- Comprehensive API resources for data transformation

## Documentation

- [Quick Setup Guide](SETUP.md) - Detailed setup instructions
- [Project Plan](PROJECT_PLAN.md) - Development roadmap
- [API Documentation](http://localhost/docs) - Interactive API docs (when running)

## Testing

```bash
# Run all tests
./vendor/bin/sail test

# Run specific test suite
./vendor/bin/sail test tests/Feature/Api/V1/

# Run with coverage
./vendor/bin/sail test --coverage
```

## Development

```bash
# Format code
./vendor/bin/sail pint

# Start Horizon for queue processing
./vendor/bin/sail artisan horizon

# Clear caches
./vendor/bin/sail artisan cache:clear

# Fresh database
make fresh
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
