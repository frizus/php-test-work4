install-libs:
	composer install

setup: install-libs run-composer-scripts init-db generate-reviews-sql

run-composer-scripts:
	composer run-script post-root-package-install

init-db:
	php artisan app:import-sql

generate-reviews-sql:
	php artisan app:generate-reviews-sql

import-reviews-sql:
	php artisan app:import-sql reviews.xlsx.sql

linter:
	./vendor/bin/php-cs-fixer fix .

test:
	# Тесты не написаны

lint: linter

.PHONY: test
