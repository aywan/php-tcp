.PHONY: build
build:
	docker-compose build


.PHONY: up
up:
	docker-compose up -d

.PHONY: stop
stop:
	docker-compose stop

.PHONY: down
down:
	docker-compose down --remove-orphans

.PHONY: shell
shell:
	docker-compose exec php sudo -u www-data bash