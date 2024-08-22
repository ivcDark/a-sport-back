# Указываем базовый образ PHP 8.1 с fpm
FROM php:8.1-fpm

# Устанавливаем необходимые пакеты и расширения
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    unzip \
    nginx \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo_mysql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем файлы проекта
COPY . /var/www

# Устанавливаем права на директории
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Указываем рабочую директорию
WORKDIR /var/www

# Устанавливаем зависимости проекта
RUN composer install

# Копируем файл конфигурации nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Экспонируем порты
EXPOSE 80

# Запускаем PHP-FPM и Nginx
CMD service php8.1-fpm start && nginx -g "daemon off;"
