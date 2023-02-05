# Test Task

##Requirements:
- [Docker](https://docs.docker.com/engine/install/ubuntu/)
- [Docker-compose](https://docs.docker.com/compose/install/)

##Installation:
 - Run `docker-compose exec php-fpm /bin/bash` to pass into container
 - Inside container run `composer install` to install dependencies
 - Copy config file `cp .env.example .env`

##Run command:
 - Inside container run  `php bin/console app:calculate-fees test.csv`

##Run test:
 - Inside container run `php vendor/bin/phpunit`
