build:
	mkdir tmp
	mkdir tmp/db

run:
	docker-compose up -d
	docker exec a_sport_app_2 git config --global --add safe.directory /var/www
	docker exec a_sport_app_2 git stash
	docker exec a_sport_app_2 git pull origin main
	docker exec a_sport_app_2 composer update
	docker exec a_sport_app_2 php artisan migrate
	docker exec a_sport_app_2 chmod -R 777 /var/www/storage/logs
	docker exec a_sport_app_2 php artisan optimize:clear

stop:
	docker-compose down
