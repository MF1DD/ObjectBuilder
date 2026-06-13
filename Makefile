.DEFAULT_GOAL := help

DOCKER := docker-compose run --rm -T app

## —— The Makefile ———————————————————————————————————
.PHONY: help
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Tests ——————————————————————————————————————————
.PHONY: phpunit
phpunit: ## Run all unit/integration tests
	$(DOCKER) vendor/bin/phpunit

.PHONY: test
test: phpunit ## Alias for phpunit

.PHONY: test-coverage
test-coverage: ## Run tests with HTML/text coverage report
	$(DOCKER) vendor/bin/phpunit --coverage-html reports/ --coverage-text

## —— Static Analysis —————————————————————————————————
.PHONY: phpstan
phpstan: ## Run PHPStan (level 7)
	$(DOCKER) vendor/bin/phpstan analyse -c .qa/phpstan/phpstan.neon --memory-limit=512M

.PHONY: psalm
psalm: ## Run Psalm (level 5)
	$(DOCKER) vendor/bin/psalm -c .qa/psalm/psalm.xml --no-progress --show-info=false

.PHONY: analyse
analyse: phpstan psalm ## Run all static analysis

## —— Code Quality ————————————————————————————————————
.PHONY: rector
rector: ## Run Rector (dry-run)
	$(DOCKER) vendor/bin/rector process src/ -c .qa/rector/rector.php --dry-run --no-progress-bar

.PHONY: rector-fix
rector-fix: ## Apply Rector fixes
	$(DOCKER) vendor/bin/rector process src/ -c .qa/rector/rector.php --no-progress-bar

.PHONY: ecs
ecs: ## Run Easy Coding Standard
	$(DOCKER) vendor/bin/ecs check src/ tests/ -c .qa/ecs/ecs.php

.PHONY: ecs-fix
ecs-fix: ## Apply ECS fixes
	$(DOCKER) vendor/bin/ecs check src/ tests/ -c .qa/ecs/ecs.php --fix

## —— Architecture ————————————————————————————————————
.PHONY: deptrac
deptrac: ## Run Deptrac architecture checks
	$(DOCKER) vendor/bin/deptrac analyse --config-file .qa/deptrac/deptrac.yaml --no-progress

.PHONY: deptrac-baseline
deptrac-baseline: ## Regenerate Deptrac baseline
	$(DOCKER) bash -c "cp .qa/deptrac/deptrac.yaml /tmp/d.yaml && sed -i '1,2d' /tmp/d.yaml && vendor/bin/deptrac analyse --config-file /tmp/d.yaml --no-progress --formatter=baseline && mv deptrac.baseline.yaml .qa/deptrac/deptrac.baseline.yaml"

## —— Mutation Testing ————————————————————————————————
.PHONY: infection
infection: ## Run Infection mutation testing
	$(DOCKER) vendor/bin/infection --configuration=.qa/infection/infection.json5 --threads=4

## —— Security ————————————————————————————————————————
.PHONY: audit
audit: ## Run Composer security audit
	$(DOCKER) composer audit

## —— All —————————————————————————————————————————————
.PHONY: check
check: phpunit phpstan psalm deptrac audit ## Run all checks (tests + static analysis + architecture + security)

.PHONY: ci
ci: phpunit phpstan psalm deptrac rector audit ## Full CI pipeline (including Rector dry-run)
