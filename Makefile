.PHONY: help setup up down restart shell artisan test pint fresh logs fetch horizon build dev clean cache-clear install status

help: ## Show this help message
	@echo 'News Aggregator - Available Commands'
	@echo ''
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'
	@echo ''

setup: ## Complete initial setup (run once)
	@echo "=========================================="
	@echo "News Aggregator - Initial Setup"
	@echo "=========================================="
	@echo ""
	@if ! docker info > /dev/null 2>&1; then \
		echo "Error: Docker is not running. Please start Docker Desktop and try again."; \
		exit 1; \
	fi
	@echo "Docker is running"
	@echo ""
	@if [ ! -f .env ]; then \
		echo "Creating .env file..."; \
		cp .env.example .env; \
	else \
		echo ".env file exists"; \
	fi
	@echo ""
	@echo "Creating storage directories..."
	@mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/framework/testing storage/logs storage/app/public bootstrap/cache
	@chmod -R 777 storage bootstrap/cache
	@echo ""
	@echo "Starting Docker containers..."
	@./vendor/bin/sail up -d
	@echo ""
	@echo "Waiting for MySQL..."
	@until ./vendor/bin/sail exec mysql mysqladmin ping -h"localhost" --silent 2>/dev/null; do \
		sleep 2; \
	done
	@echo "MySQL ready"
	@echo ""
	@echo "Waiting for Redis..."
	@until ./vendor/bin/sail exec redis redis-cli ping > /dev/null 2>&1; do \
		sleep 2; \
	done
	@echo "Redis ready"
	@echo ""
	@echo "Creating storage directories in container..."
	@./vendor/bin/sail exec laravel.test bash -c "mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/framework/testing storage/logs storage/app/public && chmod -R 777 storage bootstrap/cache"
	@echo ""
	@echo "Clearing cached configuration..."
	@./vendor/bin/sail artisan config:clear
	@./vendor/bin/sail artisan cache:clear
	@echo ""
	@echo "Generating application key..."
	@./vendor/bin/sail artisan key:generate --force
	@echo ""
	@echo "Running database migrations..."
	@./vendor/bin/sail artisan migrate --force
	@echo ""
	@echo "Seeding database..."
	@./vendor/bin/sail artisan db:seed --force
	@echo ""
	@echo "Creating storage link..."
	@./vendor/bin/sail artisan storage:link
	@echo ""
	@echo "Installing NPM dependencies..."
	@./vendor/bin/sail npm install
	@echo ""
	@echo "Building frontend assets..."
	@./vendor/bin/sail npm run build
	@echo ""
	@echo "Fetching initial articles..."
	@./vendor/bin/sail artisan news:fetch
	@echo ""
	@echo "=========================================="
	@echo "Setup Complete!"
	@echo "=========================================="
	@echo ""
	@echo "Application: http://localhost:8080"
	@echo "API Docs: http://localhost:8080/docs"
	@echo "Horizon: http://localhost:8080/horizon"
	@echo ""

install: ## Install composer and npm dependencies
	@./vendor/bin/sail composer install
	@./vendor/bin/sail npm install

up: ## Start all containers
	@./vendor/bin/sail up -d

down: ## Stop all containers
	@./vendor/bin/sail down

restart: ## Restart all containers
	@./vendor/bin/sail restart

status: ## Show container status
	@./vendor/bin/sail ps

logs: ## Show container logs (usage: make logs service=laravel.test)
	@./vendor/bin/sail logs -f $(service)

shell: ## Access application container shell
	@./vendor/bin/sail shell

artisan: ## Run artisan command (usage: make artisan cmd="migrate")
	@./vendor/bin/sail artisan $(cmd)

tinker: ## Open Laravel Tinker
	@./vendor/bin/sail artisan tinker

build: ## Build frontend assets
	@./vendor/bin/sail npm run build

dev: ## Start dev server with hot reload
	@./vendor/bin/sail npm run dev

fresh: ## Fresh database with seeds
	@./vendor/bin/sail artisan migrate:fresh --seed

migrate: ## Run database migrations
	@./vendor/bin/sail artisan migrate

seed: ## Seed database
	@./vendor/bin/sail artisan db:seed

test: ## Run all tests
	@./vendor/bin/sail artisan test

test-coverage: ## Run tests with coverage
	@./vendor/bin/sail artisan test --coverage

test-filter: ## Run specific tests (usage: make test-filter name="article")
	@./vendor/bin/sail artisan test --filter=$(name)

pint: ## Format code with Laravel Pint
	@./vendor/bin/sail pint

pint-test: ## Check code formatting without fixing
	@./vendor/bin/sail pint --test

cache-clear: ## Clear all caches
	@./vendor/bin/sail artisan config:clear
	@./vendor/bin/sail artisan cache:clear
	@./vendor/bin/sail artisan view:clear
	@./vendor/bin/sail artisan route:clear

optimize: ## Optimize application for production
	@./vendor/bin/sail artisan config:cache
	@./vendor/bin/sail artisan route:cache
	@./vendor/bin/sail artisan view:cache

fetch: ## Fetch articles from all sources
	@./vendor/bin/sail artisan news:fetch

horizon: ## Start Horizon queue worker
	@./vendor/bin/sail artisan horizon

horizon-terminate: ## Terminate Horizon
	@./vendor/bin/sail artisan horizon:terminate

clean: ## Clean up containers, volumes, and caches
	@./vendor/bin/sail down -v
	@rm -rf storage/framework/cache/*
	@rm -rf storage/framework/sessions/*
	@rm -rf storage/framework/views/*
	@rm -rf bootstrap/cache/*.php

reset: ## Complete reset (down, clean, setup)
	@make down
	@make clean
	@make setup

info: ## Show application information
	@./vendor/bin/sail artisan about

routes: ## List all routes
	@./vendor/bin/sail artisan route:list

queue-stats: ## Show queue statistics
	@./vendor/bin/sail artisan queue:monitor
