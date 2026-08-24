FROM php:8.3-cli-alpine

# PCOV for code coverage — build deps are removed after compilation
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apk del --no-cache $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install dependencies in a separate layer for cache reuse
COPY composer.json ./
RUN composer install --no-interaction --prefer-dist

COPY src/        src/
COPY tests/      tests/
COPY phpunit.xml .

CMD ["vendor/bin/phpunit"]
