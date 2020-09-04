#### Run under root directory
`composer install`

#### Generate .env file
`cp .env.example .env`

#### Generate key
`php artisan key:generate`


#### Change .env database credentials and app info ( APP_URL , APP_NAME)


#### Run under root directory
`php artisan migrate --seed`


#### Get static html/css documentation code ( run under root directory)
`apidoc -i app/Http/Controllers/ -o  where_to_locate_path`