DOCKER_DIR := infrastructure/docker
DC := docker-compose
DC_BUILD := $(DC) build
DC_UP := $(DC) up
DC_DOWN := $(DC) down
DC_EXEC := $(DC) exec
PHP_CLI_CONTAINER := php-cli-resource-crawler-bundle
PHP_CLI_CONTAINER_EXEC := $(DC_EXEC) $(PHP_CLI_CONTAINER)
BASH := /bin/bash
COMPOSER := /usr/local/bin/composer
PHPMD := ./vendor/bin/phpmd
PHPCS := ./vendor/bin/phpcs
PHPSTAN := ./vendor/bin/phpstan
PHPUNIT := ./vendor/bin/phpunit

build:
	cd $(DOCKER_DIR) && $(DC_BUILD)

build-no-cache:
	cd $(DOCKER_DIR) && $(DC_BUILD) --no-cache

start:
	cd $(DOCKER_DIR) && $(DC_UP) -d

stop:
	cd $(DOCKER_DIR) && $(DC_DOWN)

ssh:
	cd $(DOCKER_DIR) && $(PHP_CLI_CONTAINER_EXEC) $(BASH)

install:
	cd $(DOCKER_DIR) && $(PHP_CLI_CONTAINER_EXEC) $(COMPOSER) install

phpmd:
	cd $(DOCKER_DIR) && $(PHP_CLI_CONTAINER_EXEC) $(PHPMD) src text cleancode,codesize,controversial,design,naming,unusedcode

phpcs:
	cd $(DOCKER_DIR) && $(PHP_CLI_CONTAINER_EXEC) $(PHPCS)

phpstan:
	cd $(DOCKER_DIR) && $(PHP_CLI_CONTAINER_EXEC) $(PHPSTAN)

phpunit:
	cd $(DOCKER_DIR) && $(PHP_CLI_CONTAINER_EXEC) $(PHPUNIT)