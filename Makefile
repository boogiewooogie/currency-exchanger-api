help:
	@echo "Available commands:"
	@echo "  make start       - Start Docker containers"
	@echo "  make stop        - Stop Docker containers"
	@echo "  make down        - Stop and remove Docker containers, networks, and volumes"
	@echo "  make install     - Install project dependencies"
	@echo "  make update      - Update project dependencies"
	@echo "  make logs        - View Docker container logs"

start:
	docker-compose up -d

stop:
	docker-compose stop

down:
	docker-compose down

install:
	docker exec php83-container composer install

update:
	docker exec php83-container composer update

logs:
	docker-compose logs -f