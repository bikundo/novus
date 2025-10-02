#!/bin/bash

# News Aggregator - Development Setup Script
# This script automates the complete setup process for new developers

set -e

echo "=========================================="
echo "News Aggregator - Development Setup"
echo "=========================================="
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Error: Docker is not running. Please start Docker Desktop and try again."
    exit 1
fi

echo "✓ Docker is running"
echo ""

# Check if .env file exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "✓ .env file created"
else
    echo "✓ .env file already exists"
fi

echo ""
echo "🐳 Starting Docker containers..."
./vendor/bin/sail up -d

echo ""
echo "⏳ Waiting for services to be healthy..."
sleep 10

echo ""
echo "🔑 Generating application key..."
./vendor/bin/sail artisan key:generate

echo ""
echo "🗄️  Running database migrations..."
./vendor/bin/sail artisan migrate --force

echo ""
echo "📊 Seeding database with initial data..."
./vendor/bin/sail artisan db:seed --force

echo ""
echo "🔍 Setting up Typesense search index..."
./vendor/bin/sail artisan scout:sync-index-settings
./vendor/bin/sail artisan scout:import "App\Models\Article"

echo ""
echo "📦 Installing NPM dependencies..."
./vendor/bin/sail npm install

echo ""
echo "🎨 Building frontend assets..."
./vendor/bin/sail npm run build

echo ""
echo "=========================================="
echo "✅ Setup Complete!"
echo "=========================================="
echo ""
echo "Your News Aggregator is ready!"
echo ""
echo "🌐 Application URL: http://localhost"
echo "📚 API Documentation: http://localhost/docs"
echo "🚀 Horizon Dashboard: http://localhost/horizon"
echo ""
echo "Useful commands:"
echo "  ./vendor/bin/sail up       - Start all containers"
echo "  ./vendor/bin/sail down     - Stop all containers"
echo "  ./vendor/bin/sail artisan  - Run artisan commands"
echo "  ./vendor/bin/sail test     - Run tests"
echo "  ./vendor/bin/sail shell    - Access container shell"
echo ""
echo "To fetch articles from news sources:"
echo "  ./vendor/bin/sail artisan news:fetch"
echo ""
echo "Happy coding! 🎉"
echo ""

