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
 
 ##Env variables:
 - `CURRENCY_EXCHANGE_API_URL` - Api url of currency rates
 - `DEFAULT_CURRENCY` - Default currency
 - `DEFAULT_RATES_MODE` - Determines whether it is necessary to make requests to the currency rate server. Enum (`1`, `0`)
 - `DEFAULT_RATES_FILE_PATH` - Default currency rates file path
 - `DEPOSIT_PERCENT_FEE` - Percent of deposit amount to charge
 - `PRIVATE_CLIENT_FREE_AMOUNT` - Weekly free withdrawals
 - `PRIVATE_CLIENT_FREE_WITHDRAWS` - Weekly withdrawal limit
 - `FEE_PRECISION` - Number of decimal places when rounding
 - `WITHDRAW_FEE_FOR_PRIVATE_CLIENT` - Commission fee for private clients
 - `WITHDRAW_FEE_FOR_BUSINESS_CLIENT` - Commission fee for business clients 
