run:
	docker-compose up -d

copy-env:
	cp -n .env.default .env || true

full-setup: copy-env clear build
	docker-compose run --rm php bash -i -c "\
	make setup\
	"

build: copy-env
	docker-compose build

build-no-cache: copy-env
	docker-compose build --no-cache

down:
	docker-compose stop || true

clear:
	docker-compose down -v --remove-orphans || true

build-php:
	docker-compose build php

rebuild-php:
	docker-compose build --no-cache php

build-nginx:
	docker-compose build nginx

rebuild-nginx:
	docker-compose build --no-cache nginx

build-db:
	docker-compose build db

rebuild-db:
	docker-compose build --no-cache db

init-db:
	docker-compose run --rm php bash -i -c "\
	make init-db\
	"

generate-reviews-sql:
	docker-compose run --rm php bash -i -c "\
	make generate-reviews-sql\
	"

import-reviews-sql:
	docker-compose run --rm php bash -i -c "\
	make import-reviews-sql\
	"

copy-reviews-sql:
	make up
	docker-compose cp php:/home/docker_user/app/storage/reviews.xlsx.sql .
	make down

test:
	docker-compose run --rm php bash -i -c "\
	make test\
	"

connect-php:
	docker-compose exec -it php bash

start: run

launch: run

compose: run

up: run

rebuild: build-no-cache

.PHONY: test