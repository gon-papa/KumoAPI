FROM php:8.3-cli-alpine

WORKDIR /app

# Tools commonly needed for PHP dev and Composer
RUN apk add --no-cache git curl unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV APP_DOCROOT=public

COPY . /app

EXPOSE 8000

# Use PHP's built-in server for quick development. Override APP_DOCROOT if needed.
CMD ["sh", "-c", "php -S 0.0.0.0:8000 -t ${APP_DOCROOT}"]
