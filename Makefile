PACKAGE=Arara/Process
SOURCE=src/
TEST=tests/
VERSION=$(shell git describe --always)

.title:
	@echo "\033[32m${PACKAGE}\033[m - \033[33m${VERSION}\033[m"

install: .title
	@test ! -f composer.phar && curl -sS https://getcomposer.org/installer | php || composer.phar self-update
	@php composer.phar install

phpcs: .title
	@vendor/bin/phpcs --standard=PSR2 "${SOURCE}"

phpmd: .title
	@vendor/bin/phpmd "${SOURCE}" text codesize,controversial,design,naming,unusedcode

phpunit: .title
	@vendor/bin/phpunit "${TEST}"

phpunit-coverage-html: .title
	@vendor/bin/phpunit --coverage-html=build/coverage "${TEST}"

phpunit-coverage-text: .title
	@vendor/bin/phpunit --coverage-text "${TEST}"

phpunit-testdox: .title
	@vendor/bin/phpunit --testdox "${TEST}"
