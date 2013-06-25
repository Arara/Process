.title:
	@echo "Arara/Process"
	@echo

test: .title install
	@./vendor/bin/phpunit

test-coverage: .title install
	@./vendor/bin/phpunit --coverage-html=build/coverage

composer-clean: .title
	@echo "Cleaning composer and its dependencies..."
	@test -f composer.phar && rm composer.phar || echo "'composer.phar' file does not exists"
	@test -f composer.lock && rm composer.lock || echo "'composer.lock' file does not exists"
	@test -d vendor && rm -rf vendor || echo "'vendor' directory does not exists"

composer-install: .title
	@test -f composer.phar || curl -sS https://getcomposer.org/installer | php

install: .title composer-install
	@test -d vendor || ./composer.phar install --dev
