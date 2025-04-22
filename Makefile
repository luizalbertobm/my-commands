PHP := php

phpstan:
	$(PHP) vendor/bin/phpstan analyse src --level 8

fixer:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src 

check: fixer phpstan
