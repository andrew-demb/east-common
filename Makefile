### Variables

# Applications
COMPOSER ?= /usr/bin/env composer
DEPENDENCIES ?= lastest
PHP ?= /usr/bin/env php

### Helpers
all: clean depend

.PHONY: all

### Dependencies
depend:
ifeq ($(DEPENDENCIES), lowest)
	${COMPOSER} update --prefer-lowest --prefer-dist --no-interaction;
else
	${COMPOSER} update --prefer-dist --no-interaction;
endif

.PHONY: depend

### QA
qa: lint phpstan phpcs phpcpd composerunsed

lint:
	find ./src -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./infrastructures -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0

phploc:
	${PHP} vendor/bin/phploc src infrastructures

phpstan:
	${PHP} -d memory_limit=256M vendor/bin/phpstan analyse src infrastructures --level max

phpcs:
	${PHP} vendor/bin/phpcs --standard=PSR12 --ignore=src/Query/Enum/Direction.php,src/Object/BlockType.php --extensions=php src/ infrastructures/

phpcpd:
	${PHP} vendor/bin/phpcpd src/ infrastructures/

composerunsed:
	${PHP} vendor/bin/composer-unused

.PHONY: qa lint phploc phpstan phpcs phpcpd composerunsed

### Testing
test:
	XDEBUG_MODE=coverage ${PHP} -dzend_extension=xdebug.so -dxdebug.coverage_enable=1 vendor/bin/phpunit -c phpunit.xml -v --colors --coverage-text
	${PHP} vendor/bin/behat
	rm -rf tests/var/cache/

.PHONY: test

### Cleaning
clean:
	rm -rf vendor
	rm -rf tests/var/cache/*

.PHONY: clean
