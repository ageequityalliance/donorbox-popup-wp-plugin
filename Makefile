PLUGIN_NAME := donorbox-popup

PHP_IMAGE := php:8-alpine@sha256:b2d756925367bd8a5d6e0edc243134ed474844b6226379897fa70fea16b74133
COMPOSER_DOCKER_IMAGE := composer@sha256:ad6386576ec6f11b8fd1e9f9ea9b375c21ddf3cc941be94d1dd41063f43166f7

BUILD_DIR := ./build
VENDOR_BIN_DIR := /workspace/vendor/bin

DOCKER_RUN := docker run --rm -v `pwd`:/workspace -w /workspace

.PHONY: all
all: composer_install lint test

.PHONY: clean
clean:
	rm -rf vendor

.PHONY: lint
lint: vendor
	$(DOCKER_RUN) --entrypoint "$(VENDOR_BIN_DIR)/phpcs" $(PHP_IMAGE) .

.PHONY: format
format: vendor
	$(DOCKER_RUN) --entrypoint "$(VENDOR_BIN_DIR)/phpcbf" $(PHP_IMAGE) .

vendor:
	$(DOCKER_RUN) $(COMPOSER_DOCKER_IMAGE) install

.PHONY: composer_update
composer_update:
	$(DOCKER_RUN) $(COMPOSER_DOCKER_IMAGE) update

.PHONY: test
test:
	$(DOCKER_RUN) $(WP_TEST_IMAGE) ./vendor/bin/phpunit --testsuite="integration"

.PHONY: get_version
get_version:
	@awk '/Version:/{printf $$NF}' $(PLUGIN_NAME).php

.PHONY: build
build:
	@rm -rf $(BUILD_DIR)/$(PLUGIN_NAME)
	@rm -rf $(BUILD_DIR)/$(PLUGIN_NAME)-$(shell make get_version).zip
	@mkdir -p $(BUILD_DIR)/$(PLUGIN_NAME)
	@rsync -rR $(PLUGIN_NAME).php $(PLUGIN_NAME).js $(BUILD_DIR)/$(PLUGIN_NAME)/
	@cd $(BUILD_DIR)/ && zip -r $(PLUGIN_NAME)-$(shell make get_version).zip $(PLUGIN_NAME)

.PHONY: wordpress_org_deploy
wordpress_org_deploy:
	# passing this for now