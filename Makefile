.DEFAULT_GOAL := help

# Current user ID and group ID except MacOS where it conflicts with Docker abilities
ifeq ($(shell uname), Darwin)
    export UID=1000
    export GID=1000
else
    export UID=$(shell id -u)
    export GID=$(shell id -g)
endif

export COMPOSE_PROJECT_NAME=demo-blog

init: composer-install npm-install

composer-install:
	docker compose run --rm app composer install

composer-update:
	docker compose run --rm app composer update

npm-install:
	docker compose run --rm app npm install

build:
	docker compose build

up:
	docker compose up -d

down:
	docker compose down --remove-orphans

clear:
	docker compose down --volumes --remove-orphans

shell:
	docker compose exec app /bin/bash

test:
	docker compose run --rm app composer test

psalm:
	docker compose run --rm app composer psalm

rector:
	docker compose run --rm app composer rector

add-fixture-access:
	docker compose run --rm app ./yii fixture:addAccess

add-fixture:
	docker compose run --rm app ./yii fixture:add ${n:-20}

assign-role:
	docker compose run --rm app ./yii assign:addRole ${user_id} admin

# Output the help for each task, see https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
help: ## This help.
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
