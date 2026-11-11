FROM dunglas/frankenphp:1-php8.4-trixie

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN install-php-extensions pdo_mysql pdo_pgsql mbstring bcmath intl zip

WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && php artisan storage:link

COPY Caddyfile /etc/frankenphp/Caddyfile
EXPOSE 8000
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
