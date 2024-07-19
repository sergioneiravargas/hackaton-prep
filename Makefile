# VARIABLES
PROJECT_NAME := hackaton-prep

# SETUP
.PHONY: setup
setup: setup-docker-env up setup-app-env setup-app

.PHONY: setup-dev
setup-dev: setup-docker-env up-dev setup-app-env-dev setup-app-dev

.PHONY: setup-docker-env
setup-docker-env:
	@> docker/.env && \
	echo CONTAINER_NAME=${PROJECT_NAME} >> docker/.env && \
	echo 'CONTAINER_PUID:' && read container_puid && echo CONTAINER_PUID=$${container_puid} >> docker/.env && \
	echo 'CONTAINER_PGID:' && read container_pgid && echo CONTAINER_PGID=$${container_pgid} >> docker/.env && \
	echo 'NGINX_HTTP_PORT:' && read nginx_http_port && echo NGINX_HTTP_PORT=$${nginx_http_port} >> docker/.env && \
	echo 'NGINX_HTTPS_PORT:' && read nginx_https_port && echo NGINX_HTTPS_PORT=$${nginx_https_port} >> docker/.env && \
	echo 'POSTGRES_PORT (dev only, empty otherwise):' && read postgres_port && echo POSTGRES_PORT=$${postgres_port} >> docker/.env && \
	echo 'ADMINER_PORT (dev only, empty otherwise):' && read adminer_port && echo ADMINER_PORT=$${adminer_port} >> docker/.env && \
	echo 'APP_IMAGE (prod only, empty otherwise):' && read app_image && echo APP_IMAGE=$${app_image} >> docker/.env && \
	echo 'APP_WORKER_IMAGE (prod only, empty otherwise):' && read app_worker_image && echo APP_WORKER_IMAGE=$${app_worker_image} >> docker/.env && \
	echo 'NGINX_IMAGE (prod only, empty otherwise):' && read nginx_image && echo NGINX_IMAGE=$${nginx_image} >> docker/.env

.PHONY: setup-app-env
setup-app-env:
	@> app/.env.local && \
	echo 'APP_ENV=prod' >> app/.env.local && \
	echo 'APP_SECRET:' && read app_secret && echo APP_SECRET=$${app_secret} >> app/.env.local && \
	echo 'APP_BASE_URL:' && read app_base_url && echo APP_BASE_URL=$${app_base_url} >> app/.env.local && \
	echo 'DATABASE_URL:' && read database_url && echo DATABASE_URL=$${database_url} >> app/.env.local && \
	echo 'MAILER_DSN:' && read mailer_dsn && echo MAILER_DSN=$${mailer_dsn} >> app/.env.local && \
	echo 'EMAIL_SENDER_ADDRESS:' && read email_sender_address && echo EMAIL_SENDER_ADDRESS=$${email_sender_address} >> app/.env.local && \
	echo 'EMAIL_SENDER_NAME:' && read email_sender_name && echo EMAIL_SENDER_NAME=$${email_sender_name} >> app/.env.local && \
	echo 'USER_EMAIL_VERIFICATION_ROUTE:' && read user_email_verification_route && echo USER_EMAIL_VERIFICATION_ROUTE=$${user_email_verification_route} >> app/.env.local && \
	echo 'USER_EMAIL_VERIFICATION_TOKEN_TTL:' && read user_email_verification_token_ttl && echo USER_EMAIL_VERIFICATION_TOKEN_TTL=$${user_email_verification_token_ttl} >> app/.env.local && \
	echo 'USER_PASSWORD_RECOVERY_ROUTE:' && read user_password_recovery_route && echo USER_PASSWORD_RECOVERY_ROUTE=$${user_password_recovery_route} >> app/.env.local && \
	echo 'USER_PASSWORD_RECOVERY_TOKEN_TTL:' && read user_password_recovery_token_ttl && echo USER_PASSWORD_RECOVERY_TOKEN_TTL=$${user_password_recovery_token_ttl} >> app/.env.local

.PHONY: setup-app
setup-app:
	@docker exec -it ${PROJECT_NAME}.app ash -c 'composer install --no-dev -o' && \
	docker exec -it ${PROJECT_NAME}.app ash -c '/var/www/app/bin/console doctrine:database:create; /var/www/app/bin/console doctrine:migrations:migrate'

.PHONY: setup-app-env-dev
setup-app-env-dev:
	@> app/.env.local && \
	echo 'APP_ENV=dev' >> app/.env.local && \
	echo 'APP_BASE_URL:' && read app_base_url && echo APP_BASE_URL=$${app_base_url} >> app/.env.local && \
	echo 'DATABASE_URL:' && read database_url && echo DATABASE_URL=$${database_url} >> app/.env.local

.PHONY: setup-app-dev
setup-app-dev:
	@docker exec -it ${PROJECT_NAME}.app ash -c 'composer install' && \
	docker exec -it ${PROJECT_NAME}.app ash -c '/var/www/app/bin/console doctrine:database:create; /var/www/app/bin/console doctrine:schema:update -f; /var/www/app/bin/console doctrine:fixtures:load'

# DOCKER CONTAINERS
.PHONY: up
up:
	@docker compose -f docker/docker-compose.yaml up -d --remove-orphans

.PHONY: up-dev
up-dev:
	@docker compose -f docker/docker-compose.dev.yaml up -d --remove-orphans

.PHONY: down
down:
	@docker compose -f docker/docker-compose.yaml down

.PHONY: down-dev
down-dev:
	@docker compose -f docker/docker-compose.dev.yaml down

.PHONY: restart
restart:
	@echo 'service name:' && \
	read service_name && \
	docker restart ${PROJECT_NAME}.$${service_name}

.PHONY: build
build:
	@docker compose -f docker/docker-compose.yaml --env-file docker/.env build --no-cache

.PHONY: build-dev
build-dev:
	@docker compose -f docker/docker-compose.dev.yaml --env-file docker/.env build --no-cache

.PHONY: exec
exec:
	@echo 'service name:' && \
	read service_name && \
	docker exec -it ${PROJECT_NAME}.$${service_name} ash

.PHONY: logs
logs:
	@echo 'service name:' && \
	read service_name && \
	docker logs -f -n 50 ${PROJECT_NAME}.$${service_name}

.PHONY: stats
stats:
	@echo 'service name:' && \
	read service_name && \
	docker stats ${PROJECT_NAME}.$${service_name}

# APP
.PHONY: test
test:
	@docker exec -it ${PROJECT_NAME}.app bin/console d:s:u -f --env test && \
	docker exec -it ${PROJECT_NAME}.app bin/phpunit

.PHONY: csfix
csfix:
	@docker exec -it ${PROJECT_NAME}.app tools/php-cs-fixer/vendor/bin/php-cs-fixer fix

.PHONY: loc
loc:
	@find ./app/src ./app/test -name '*.php' | xargs wc -l | tail -1
