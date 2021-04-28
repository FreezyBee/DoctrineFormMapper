default: cs phpstan test

test:
	php vendor/bin/tester tests ./src

phpstan:
	php vendor/bin/phpstan

cs:
	php vendor/bin/phpcs --standard=PSR12 ./src ./tests
