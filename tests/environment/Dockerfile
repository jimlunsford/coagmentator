ARG COMPOSER_IMAGE
ARG PHP_IMAGE
FROM ${COMPOSER_IMAGE} AS composer
FROM ${PHP_IMAGE}
RUN apt-get update && apt-get install -y --no-install-recommends git unzip curl libonig-dev libxml2-dev \
    && docker-php-ext-install -j2 mysqli mbstring \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /workspace
