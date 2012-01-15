default: help

help:
	@echo "\033[1;32mJam/Process\n\033[0m"
	@echo "Targets:"
	@echo "     deps    Install project dependences"
	@echo "     help    Displays this help"
	@echo "     phing   Rungs a phing target. (Example: make phing target=qa)"
	@echo

deps:
	@echo "Installing PEAR packages"
	pear upgrade
	pear config-set auto_discover 1
	pear install --soft --force pear.docblox-project.org/DocBlox-beta
	pear install --soft --force pear.pdepend.org/PHP_Depend-beta
	pear install --soft --force pear.phing.info/phing
	pear install --soft --force pear.phpmd.org/PHP_PMD
	pear install --soft --force pear.phpunit.de/phpcpd
	pear install --soft --force pear.phpunit.de/phpdcd-beta
	pear install --soft --force pear.phpunit.de/phploc
	pear install --soft --force pear.phpunit.de/PHPUnit
	pear install --soft --force pear.phpunit.de/PHP_CodeBrowser
	pear install --soft --force pear.php.net/PHP_CodeSniffer

phing:
	@echo "Running PHing"
	phing $(target)

test:
	@echo "Running tests"
	@git submodule update --init
	@phpunit --colors