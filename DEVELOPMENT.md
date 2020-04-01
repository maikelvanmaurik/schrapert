# Environment

Create a copy of the `.env.example` file and name it
`.env`. You can change the environment variables if you
like.

Build the docker containers using `docker-compose build`.

# Testing

Bring up the docker containers using `docker-compose up`
or use `docker-compose up -d`.

To run the tests use

`docker-compose run --rm -w /var/www/html app ./vendor/bin/phpunit`