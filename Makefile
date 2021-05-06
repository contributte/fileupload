.PHONY: install qa cs csf phpstan tests coverage-clover coverage-html

install:
	composer update

cs:
	vendor/bin/codesniffer src tests

csf:
	vendor/bin/codefixer src tests
