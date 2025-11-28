APP_DOCROOT ?= src/public     # php -S のドキュメントルート
DOCKER_IMAGE ?= kumoapi-php

.PHONY: build run up down sh composer-install composer-update

build:                     # Docker イメージを作成
	docker build -t $(DOCKER_IMAGE) .

run: build                 # 単発でコンテナ起動（ポート 8000）
	docker run --rm -p 8000:8000 -v "$(PWD)":/app -e APP_DOCROOT=$(APP_DOCROOT) $(DOCKER_IMAGE)

up:                        # docker-compose で常駐起動
	docker-compose up --build

down:                      # docker-compose 停止
	docker-compose down

sh:                        # php サービスでシェル
	docker-compose run --rm -e APP_DOCROOT=$(APP_DOCROOT) php sh

composer-install:          # 依存をインストール
	docker-compose run --rm -e APP_DOCROOT=$(APP_DOCROOT) php composer install

composer-update:           # 依存を更新
	docker-compose run --rm -e APP_DOCROOT=$(APP_DOCROOT) php composer update

fix: # フォーマット
	docker-compose run --rm -e APP_DOCROOT=$(APP_DOCROOT) php vendor/bin/php-cs-fixer fix