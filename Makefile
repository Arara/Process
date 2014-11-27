ARGS=
PACKAGE_NAME=Arara\Process
PACKAGE_SOURCE=src/
PACKAGE_VERSION=$(shell git describe --always)

.title:
	@echo "\033[32m${PACKAGE_NAME}\033[m - \033[33m${PACKAGE_VERSION}\033[m"

composer: .title
	@test ! -f composer.phar && curl -sS https://getcomposer.org/installer | php || php composer.phar self-update
	php composer.phar install ${ARGS}

phpcs: .title
	vendor/bin/phpcs --standard=PSR2 ${ARGS} "${PACKAGE_SOURCE}"

phpmd: .title
	vendor/bin/phpmd "${PACKAGE_SOURCE}" text codesize,controversial,design,naming,unusedcode ${ARGS}

phpunit: .title
	vendor/bin/phpunit --configuration phpunit.xml --colors ${ARGS}

quality-assurance: .title phpcs phpmd phpunit
