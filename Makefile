APP_NAME ?= luggage-api
DOCKER_NETWORK ?= luggage-api

up:
	docker-compose up -d

down:
	docker-compose down

clear:
	docker-compose down -v

init:
	docker-compose run --rm secured-dinosors composer install --no-interaction
