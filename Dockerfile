FROM php:8.2-fpm-alpine

# 1. Устанавливаем зависимости
RUN apk update && apk add --no-cache \
    nginx \
    nodejs \
    npm \
    oniguruma-dev \
    postgresql-dev \
    libzip-dev \
    && docker-php-ext-install zip

# 2. Устанавливаем PHP расширения
RUN docker-php-ext-install pdo pdo_mysql mbstring pdo_pgsql

# 3. Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Создаем ВСЕ необходимые директории ДО копирования кода
RUN mkdir -p /run/nginx \
    /var/lib/nginx/logs \
    /var/lib/nginx/tmp/client_body \
    /var/lib/nginx/tmp/proxy \
    /var/lib/nginx/tmp/fastcgi \
    /var/lib/nginx/tmp/uwsgi \
    /var/lib/nginx/tmp/scgi

# 5. Устанавливаем права для Nginx
RUN chown -R nginx:nginx /var/lib/nginx \
    && chmod -R 755 /var/lib/nginx

# 6. Копируем код
WORKDIR /var/www
COPY . .

# 7. Копируем .env если его нет (для Render)
COPY .env.example .env

# 8. Устанавливаем зависимости
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts \
    && npm install --legacy-peer-deps \
    && npm run build

# 9. Права для Laravel
RUN chmod -R 777 storage bootstrap/cache

# 10. Генерируем ключ приложения
RUN php artisan key:generate --no-interaction

# 11. Копируем nginx конфиг
COPY docker/nginx.conf /etc/nginx/nginx.conf

EXPOSE 80

# 12. Запускаем как root (nginx требует root для некоторых операций)
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"