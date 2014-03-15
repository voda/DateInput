all: test coverage

test:
	./vendor/bin/tester -c tests/php.ini tests

coverage:
	./vendor/bin/coverage-report -c build/coverage.dat -o build/coverage.html -s src
