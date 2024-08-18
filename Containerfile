FROM docker.io/library/php:8.0.30-cli-alpine3.16

LABEL org.opencontainers.image.url https://github.com/zaboal/bru_tg_bonus

ADD src /usr/src/app
WORKDIR /usr/src/app

RUN apk install composer
RUN composer install

ENTRYPOINT ["php", "polling-bot.php"]