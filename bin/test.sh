#!/usr/bin/env bash

docker-compose down -t 0 &> /dev/null
docker-compose up -d

if docker run -it -w /data -v ${PWD}:/data:delegated --entrypoint vendor/bin/phpunit \
   --env CI=1 --env DB_HOST=docker.for.mac.localhost --env DB_USERNAME=root \
   --env REDIS_HOST=docker.for.mac.localhost --env REDIS_PORT=6379 \
   --env MEMCACHED_HOST=docker.for.mac.localhost --env MEMCACHED_PORT=11211 \
   --rm registry.gitlab.com/grahamcampbell/php:7.4-base "$@"; then
    docker-compose down -t 0
else
    docker-compose down -t 0
    exit 1
fi