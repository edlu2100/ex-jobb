web: vendor/bin/heroku-php-nginx -C .devops/heroku/nginx-site.conf public/
worker: php artisan queue:work --queue=web-scans --sleep=3 --tries=3
