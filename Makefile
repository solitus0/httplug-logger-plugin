.PHONY: rector, bump, patch, minor

ecs_check:
	vendor/bin/ecs check --config=ecs.php --clear-cache

phpstan:
	mkdir -p ./var/phpstan
	php -d memory_limit=1G vendor/bin/phpstan analyse -c phpstan.dist.neon --error-format gitlab > ./var/phpstan/phpstan-report.json

ecs:
	vendor/bin/ecs check --config=ecs.php --clear-cache --fix

rector:
	vendor/bin/rector process

all:
	make phpstan
	make ecs
	make rector

patch:
	python3 bump_version.py patch

minor:
	python3 bump_version.py minor

test:
	APP_ENV=test php -d xdebug.mode=off -d memory_limit=-1 vendor/bin/phpunit --configuration ./phpunit.xml.dist --no-coverage
