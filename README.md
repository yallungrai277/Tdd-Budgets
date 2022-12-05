## Laravel Testing Series (APIS and BLADE)

## Requirements

-   Docker installed locally and running

### Installation

-   clone the repo
-   sail composer install
-   sail npm run dev
-   cp .env.example .env
-   sail artisan key:generate
-   configure .env files
-   sail artisan migrate
-   sail artisan db:seeed

### Run tests

-   sail artisan test
-   sail artisan test --filter={Test_class_name,or_individual_test_method}

### Queue workes

-   sail artisan queue:work

### More info

Please visit laravel sail docs on more info. Feel free to customize docker-compose.yml according to needs.
