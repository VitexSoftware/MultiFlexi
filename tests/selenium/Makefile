# vim: set tabstop=8 softtabstop=8 noexpandtab:
.PHONY: help
help: ## 📋 Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: install
install: ## 📦 Install npm dependencies
	npm install

.PHONY: test
test: ## 🧪 Run all selenium tests
	npm test

.PHONY: test-headless
test-headless: ## 🤖 Run tests in headless mode
	npm run test:headless

.PHONY: test-debug
test-debug: ## 🐛 Run tests in debug mode
	npm run test:debug

.PHONY: test-smoke
test-smoke: ## 🔥 Run smoke tests
	npm run test:smoke

.PHONY: test-pages
test-pages: ## 📄 Run page-specific tests
	npm run test:pages

.PHONY: test-scenarios
test-scenarios: ## 🎭 Run scenario tests
	npm run test:scenarios

.PHONY: test-integration
test-integration: ## 🔗 Run complete integration tests
	npm run test:integration

.PHONY: test-all
test-all: ## 🚀 Run all test suites (smoke, pages, scenarios)
	npm run test:all

.PHONY: test-ci
test-ci: ## ⚙️ Run CI-friendly test suite
	npm run test:ci

.PHONY: run-dev
run-dev: ## 💻 Run development environment tests
	./run-dev.sh

.PHONY: run-local
run-local: ## 🏠 Run local environment tests
	./run-local.sh

.PHONY: run-multi-env
run-multi-env: ## 🌐 Run multi-environment tests
	./run-multi-env.sh

.PHONY: run-scenarios
run-scenarios: ## 🎬 Run scenario-based tests
	./run-scenarios.sh

.PHONY: run-tests
run-tests: ## 🧪 Run complete test suite
	./run-tests.sh

.PHONY: clean
clean: ## 🧹 Clean node_modules and package-lock.json
	rm -rf node_modules package-lock.json
