PHP := php
PHP_INI := $(shell $(PHP) --ini | grep "Loaded Configuration File" | awk '{print $$4}')
PHP_VERSION ?= $(shell php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
DISABLE_XDEBUG_SAPIS = cli fpm apache2

# --------------------------------------------------
# Helper: lista automaticamente todos os alvos com descrição
# --------------------------------------------------
.PHONY: help
help: ## Show this help message
	@echo "Available Make targets:"; \
	grep -E '^[a-zA-Z0-9_-]+:.*##' $(MAKEFILE_LIST) \
	  | sed 's/:.*##/ :/' \
	  | awk -F ' : ' '{ printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 }'


# --------------------------------------------------
# Targets
# --------------------------------------------------
phpstan: ## Perform static analysis with PHPStan
	$(PHP) vendor/bin/phpstan analyse src --level 8

fixer: ## Fix code style with PHP CS Fixer
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src

check: ## Run all checks 
	$(MAKE) phpstan
	$(MAKE) fixer

test: ## Run all tests
	$(PHP) vendor/bin/phpunit --testdox
	
test-coverage: ## Run tests with code coverage
	$(PHP) vendor/bin/phpunit --testdox --coverage-html coverage
	# write the command to open the coverage report depending on the OS:
	@echo "Opening coverage report…"
	@if [ "$$(uname)" = "Linux" ]; then \
	  xdg-open coverage/index.html; \
	elif [ "$$(uname)" = "Darwin" ]; then \
	  open coverage/index.html; \
	elif [ "$$(uname)" = "Windows_NT" ]; then \
	  start coverage/index.html; \
	else \
	  echo "Unknown OS, please open coverage/index.html manually."; \
	fi
	@echo "✅ Coverage report opened."

xdebug.disable:	## Disable Xdebug for all configured SAPIs
	@echo "Detected PHP version: $(PHP_VERSION)"
	@for sapi in $(DISABLE_XDEBUG_SAPIS); do \
	  echo "→ Disabling xdebug for php$(PHP_VERSION)-$$sapi…"; \
	  sudo phpdismod -v $(PHP_VERSION) -s $$sapi xdebug >/dev/null 2>&1 || \
	    echo "   (xdebug not enabled for $$sapi or error)"; \
	done
	@echo "Restarting services if present…"
	-@sudo systemctl restart php$(PHP_VERSION)-fpm >/dev/null 2>&1
	-@sudo systemctl restart apache2          >/dev/null 2>&1
	@echo "✅ Xdebug has been disabled on all configured SAPIs."

xdebug.enable: ## Enable Xdebug for all configured SAPIs
	@echo "Detected PHP version: $(PHP_VERSION)"
	@for sapi in $(DISABLE_XDEBUG_SAPIS); do \
	  echo "→ Enabling xdebug for php$(PHP_VERSION)-$$sapi…"; \
	  sudo phpenmod -v $(PHP_VERSION) -s $$sapi xdebug >/dev/null 2>&1 || \
	    echo "   (xdebug not enabled for $$sapi or error)"; \
	done
	@echo "Restarting services if present…"
	-@sudo systemctl restart php$(PHP_VERSION)-fpm >/dev/null 2>&1
	-@sudo systemctl restart apache2          >/dev/null 2>&1
	@echo "✅ Xdebug has been enabled on all configured SAPIs."