SYMFONY = docker compose exec app php bin/console
#	make bash                    # Зайти в контейнер
#	make console cmd=make:user   # Запустить любую команду Symfony
#	make db-update               # Обновить базу данных
#	make fixtures                # Загрузить фикстуры
#	make fix                     # Применить php-cs-fixer

# 🔧 Открыть bash внутри PHP-контейнера
bash:
	docker compose exec app bash

# ⚙️ Запуск любой Symfony-команды
console:
	@$(SYMFONY) $(cmd)

# 🧱 Обновление схемы БД (осторожно, изменяет структуру таблиц)
db-update:
	@$(SYMFONY) doctrine:schema:update --force

# 🍭 Загрузка фикстур
fixtures:
	@$(SYMFONY) doctrine:fixtures:load --no-interaction

# ✨ Автоматическое исправление кода через PHP-CS-Fixer
fix:
	vendor/bin/php-cs-fixer fix

watch:
	npm run build:css
	npm run watch:css

show limits:
	docker compose exec app php -i | grep -E "upload_max_filesize|post_max_size"

