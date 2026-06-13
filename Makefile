.DEFAULT_GOAL := help

## —— The Makefile ———————————————————————————————————
.PHONY: help
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
## —— ✅ Tests ✅ ————————————————————————————————————————————————————————————————
.PHONY: phpunit
phpunit:
	vendor/bin/phpunit

.PHONY: test
test: phpunit ## run all tests

.PHONY: test-coverage
test-coverage: ## run unit tests and create a report
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html reports/ --coverage-text
## —— ✅ Code Style ✅ ———————————————————————————————————————————————————————————
.PHONY: phpstan
phpstan: ## run phpstan
	vendor/bin/phpstan analyse -c .qa/phpstan/phpstan.neon