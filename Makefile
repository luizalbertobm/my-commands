PHP := php

phpstan:
	$(PHP) vendor/bin/phpstan analyse src --level 8

fixer:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src 

check: fixer phpstan

test:
	$(PHP) vendor/bin/phpunit --testdox
test-coverage:
	$(PHP) vendor/bin/phpunit --testdox --coverage-html coverage
	@echo "Open coverage/index.html in your browser to view the coverage report."
	@echo "You can also use the following command to open it in your default browser:"
	@echo "open coverage/index.html"
	@echo "or"
	@echo "xdg-open coverage/index.html"
	@echo "or"
	@echo "start coverage/index.html"
	@echo "depending on your operating system."

xdebug.disable:
	$(PHP) -dxdebug.mode=off -r "echo 'Xdebug disabled successfully.' . PHP_EOL;"
	@echo "Xdebug is now disabled. You can run your tests without Xdebug."
	@echo "To enable it again, run 'make xdebug.enable'."

xdebug.enable:
	$(PHP) -dxdebug.mode=coverage -r "echo 'Xdebug enabled successfully.' . PHP_EOL;"
	@echo "Xdebug is now enabled. You can run your tests with Xdebug enabled."
	@echo "To disable it again, run 'make xdebug.disable'."