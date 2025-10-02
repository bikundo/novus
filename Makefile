.PHONY: help setup up down restart shell artisan test pint fresh logs

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

setup: ## Initial project setup (run once)
	@./setup.sh

up: ## Start all containers
	@./vendor/bin/sail up -d

down: ## Stop all containers
	@./vendor/bin/sail down

restart: ## Restart all containers
	@./vendor/bin/sail restart

shell: ## Access application container shell
	@./vendor/bin/sail shell

artisan: ## Run artisan command (usage: make artisan cmd="migrate")
	@./vendor/bin/sail artisan $(cmd)

test: ## Run tests
	@./vendor/bin/sail test

pint: ## Format code with Laravel Pint
	@./vendor/bin/sail pint

fresh: ## Fresh database with seeds
	@./vendor/bin/sail artisan migrate:fresh --seed

logs: ## Show container logs
	@./vendor/bin/sail logs -f

fetch: ## Fetch articles from all sources
	@./vendor/bin/sail artisan news:fetch

horizon: ## Start Horizon queue worker
	@./vendor/bin/sail artisan horizon

build: ## Build frontend assets
	@./vendor/bin/sail npm run build

dev: ## Start dev server
	@./vendor/bin/sail npm run dev

