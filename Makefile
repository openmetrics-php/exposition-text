.SILENT:
.PHONY: help

## This help screen
help:
	printf "Available commands\n\n"
	awk '/^[a-zA-Z\-\_0-9]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")-1); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf "\033[33m%-40s\033[0m %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

PROJECT = exposition-text
IMAGE = php80
DOCKER_COMPOSE_OPTIONS = -p $(PROJECT) -f docker-compose.yml
DOCKER_COMPOSE_BASE_COMMAND = docker-compose $(DOCKER_COMPOSE_OPTIONS)
DOCKER_COMPOSE_EXEC_COMMAND = $(DOCKER_COMPOSE_BASE_COMMAND) exec -T
DOCKER_COMPOSE_ISOLATED_RUN_COMMAND = $(DOCKER_COMPOSE_BASE_COMMAND) run --rm --no-deps

## Install whole setup
install: dcpull dcbuild composer-install
.PHONY: update

install-static-analysis: dcpull
	$(DOCKER_COMPOSE_BASE_COMMAND) build --pull --parallel composer
.PHONY: install-static-analysis

## Update whole setup
update: dcpull dcbuild composer-update
.PHONY: update

## Build all custom docker images
dcbuild: pull-extension-installer
	$(DOCKER_COMPOSE_BASE_COMMAND) build --pull --parallel
.PHONY: dcbuild

pull-extension-installer:
	docker pull mlocati/php-extension-installer
.PHONY: pull-extension-installer

## Pull docker images
dcpull:
	$(DOCKER_COMPOSE_BASE_COMMAND) pull
.PHONY: dcpull

## Tear down docker compose setup
dcdown:
	$(DOCKER_COMPOSE_BASE_COMMAND) down
.PHONY: dcdown

## Validate composer config
composer-validate:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) composer validate
.PHONY: composer-validate

## Update composer dependencies
composer-update:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) composer update --no-progress -o -vv
.PHONY: composer-update

## Install composer dependencies
composer-install:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) composer install --no-progress -o -a -vv
.PHONY: composer-install

## Run PHPStan checks
phpstan:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) phpstan analyze --memory-limit=-1 --level=8 src/
.PHONY: phpstan

## Run all tests on all PHP versions
tests: composer-validate phpstan test-php-7.2 test-php-7.3 test-php-7.4 test-php-8.0 test-php-8.1 test-php-8.2
.PHONY: tests

PHP_OPTIONS = -d error_reporting=-1 -dmemory_limit=-1 -d xdebug.mode=coverage
PHPUNIT_OPTIONS = --testdox

## Run test on PHP 7.2
test-php-7.2: dcdown
	printf "\n\033[33mRun Tests on PHP 7.2\033[0m\n"
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php72 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-9.xml --testsuite=Unit $(PHPUNIT_OPTIONS)
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php72 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-9.xml --testsuite=Integration $(PHPUNIT_OPTIONS)
.PHONY: test-php-7.2

## Run test on PHP 7.3
test-php-7.3: dcdown
	printf "\n\033[33mRun Tests on PHP 7.3\033[0m\n"
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php73 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-9.xml --testsuite=Unit $(PHPUNIT_OPTIONS)
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php73 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-9.xml --testsuite=Integration $(PHPUNIT_OPTIONS)
.PHONY: test-php-7.3

## Run test on PHP 7.4
test-php-7.4: dcdown
	printf "\n\033[33mRun Tests on PHP 7.4\033[0m\n"
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php74 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-9.xml --testsuite=Unit $(PHPUNIT_OPTIONS)
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php74 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-9.xml --testsuite=Integration $(PHPUNIT_OPTIONS)
.PHONY: test-php-7.4

## Run test on PHP 8.0
test-php-8.0: dcdown
	printf "\n\033[33mRun Tests on PHP 8.0\033[0m\n"
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php80 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-9.xml --testsuite=Unit $(PHPUNIT_OPTIONS)
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php80 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-9.xml --testsuite=Integration $(PHPUNIT_OPTIONS)
.PHONY: test-php-8.0

## Run test on PHP 8.1
test-php-8.1: dcdown
	printf "\n\033[33mRun Tests on PHP 8.1\033[0m\n"
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php81 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-10.xml --testsuite=Unit $(PHPUNIT_OPTIONS)
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php81 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-10.xml --testsuite=Integration $(PHPUNIT_OPTIONS)
.PHONY: test-php-8.1

## Run test on PHP 8.2
test-php-8.2: dcdown
	printf "\n\033[33mRun Tests on PHP 8.2\033[0m\n"
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php82 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-10.xml --testsuite=Unit $(PHPUNIT_OPTIONS)
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php82 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-10.xml --testsuite=Integration $(PHPUNIT_OPTIONS)
.PHONY: test-php-8.2

## Run test on PHP 8.3
test-php-8.3: dcdown
	printf "\n\033[33mRun Tests on PHP 8.3\033[0m\n"
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php83 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-10.xml --testsuite=Unit $(PHPUNIT_OPTIONS)
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) php83 php $(PHP_OPTIONS) /usr/local/bin/phpunit -c tests/phpunit-10.xml --testsuite=Integration $(PHPUNIT_OPTIONS)
.PHONY: test-php-8.3