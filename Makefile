SYMFONY = docker compose exec app php bin/console

bash:
	docker compose exec app bash

console:
	@$(SYMFONY)

db-update:
	@$(SYMFONY) doctrine:schema:update --force

fixtures:
	@$(SYMFONY) doctrine:fixtures:load --no-interaction

fix:
	vendor/bin/php-cs-fixer fix
