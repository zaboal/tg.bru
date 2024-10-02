FROM docker.io/library/php:8.3-alpine AS base

LABEL org.opencontainers.image.url https://github.com/zaboal/bru_tg

RUN apk add composer


# Preparing environment for development,
# further info in CONTRIBUTING.md.

FROM base AS dev

RUN apk add git python3 fish github-cli

CMD ["fish"]


# Preparing the app to be executed and executing.
# Must be placed as the latest stage in this file
# to be used by default, without target specifying.

FROM base AS prod

COPY src /usr/src/app
WORKDIR /usr/src/app

RUN composer install

# "php" and "php8" behave pretty different
ENTRYPOINT ["php8", "polling-bot.php"]