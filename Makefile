default: cs-fix phpstan tester

test: cs phpstan tester

tester:
	php vendor/bin/tester tests ./src

phpstan:
	php vendor/bin/phpstan

cs:
	php vendor/bin/ecs

cs-fix:
	php vendor/bin/ecs --fix
