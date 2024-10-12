FROM docker.io/library/php:8.3-alpine AS base

LABEL org.opencontainers.image.url=https://github.com/zaboal/bru_tg

RUN apk add composer git


# Подготовка окружения для разработки,
# подробнее в CONTRIBUTING.md.

FROM base AS dev

# для подписи коммитов
RUN apk add gpg gpg-agent openssh
RUN apk add python3 fish github-cli

CMD ["fish"]


# Подготовка приложения к запуску и запуск.
# Должно быть последней стадией в этом файле,
# чтобы использовалось по умолчанию, без указания.

FROM base AS prod

COPY src /usr/src/app
WORKDIR /usr/src/app

RUN composer install

# "php" это не "phpXX"
ENTRYPOINT ["php83", "polling-bot.php"]