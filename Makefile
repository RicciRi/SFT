SYMFONY = docker compose exec app php bin/console
#	make bash                    # Зайти в контейнер
#	make console cmd=make:user   # Запустить любую команду Symfony
#	make db-update               # Обновить базу данных
#	make fixtures                # Загрузить фикстуры
#	make fixtures-load           # Загрузить фикстуры + удаление
#	make fix                     # Применить php-cs-fixer
#   make cleanup-files  		 # Команда для очистки просроченых файлов и обозначения этого в db

bash:
	docker compose exec app bash

console:
	@$(SYMFONY) $(cmd)

fixtures:
	@$(SYMFONY) doctrine:fixtures:load --no-interaction

fixtures-load:
	@$(SYMFONY) doctrine:fixtures:load

db-update:
	@$(SYMFONY) doctrine:schema:update --force

cleanup-files:
	@$(SYMFONY) app:cleanup-expired-files

fix:
	vendor/bin/php-cs-fixer fix

watch:
	npm run build:css
	npm run watch:css

show limits:
	docker compose exec app php -i | grep -E "upload_max_filesize|post_max_size"
