all: test coverage

test:
	./vendor/bin/tester -p php -c tests/php.ini tests

coverage:
	./vendor/bin/tester -p phpdbg -c tests/php.ini --coverage build/coverage.html --coverage-src src tests
