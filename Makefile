default: cs phpstan test

test:
	php vendor/bin/tester tests ./src

phpstan:
	php vendor/bin/phpstan

cs:
	php vendor/bin/ecs

cs-fix:
	php vendor/bin/ecs --fix
