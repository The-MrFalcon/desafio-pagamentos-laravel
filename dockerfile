# Build stage para PHP e Node
FROM php:8.2-fpm-alpine AS builder

# Dependências do sistema para o build
RUN apk update && apk add --no-cache \
    git \
    unzip \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    curl \
    mysql-client \
    autoconf \
    g++ \
    make \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Composer safe ownership and flags
RUN git config --global --add safe.directory /var/www/html \
 && echo "COMPOSER_ALLOW_SUPERUSER=1" >> /etc/environment

# Instala dependências PHP (sem dev para evitar requisitos de PHP 8.3 nos pacotes de teste)
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --prefer-dist --no-interaction --no-progress -o

# Imagem final
FROM php:8.2-fpm-alpine

# Dependências do sistema
RUN apk update && apk add --no-cache \
    git \
    unzip \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    curl \
    mysql-client \
    bash \
    autoconf \
    g++ \
    make \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia o projeto
COPY . .
# Copia vendor do builder (evita rodar composer na imagem final)
COPY --from=builder /var/www/html/vendor /var/www/html/vendor
# Sem assets de frontend

# Não roda composer na imagem final

# Permissões
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
