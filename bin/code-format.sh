#!/usr/bin/env bash

docker-compose down -t 0 &> /dev/null
docker-compose up -d

if docker run -it -v $PWD:/app -w /app php ./vendor/bin/phpcbf && php ./vendor/bin/php-cs-fixer fix ./src; then
    docker-compose down -t 0
else
    docker-compose down -t 0
    exit 1
fi