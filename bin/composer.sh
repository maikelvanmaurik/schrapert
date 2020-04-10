#!/usr/bin/env bash

docker-compose down -t 0 &> /dev/null
docker-compose up -d

if docker run -it -v $PWD:/app -w /app php composer "$@"; then
    docker-compose down -t 0
else
    docker-compose down -t 0
    exit 1
fi