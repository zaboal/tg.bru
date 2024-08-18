FROM docker.io/library/php:8.0.30-cli-alpine

LABEL org.opencontainers.image.url https://github.com/zaboal/bru_tg_bonus

ADD git@github.com:zaboal/bru_tg_bonus:src /usr/src/app
WORKDIR /usr/src/app

COPY --from=composer:2.7.7 /usr/bin/composer /usr/local/bin/composer
RUN composer install

ENTRYPOINT ["php", "polling-bot.php"]