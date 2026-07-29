FROM php:8.4-fpm-alpine AS base

WORKDIR /var/www/html

RUN apk add --no-cache \
    autoconf \
    curl \
    ffmpeg \
    freetype-dev \
    g++ \
    git \
    icu-data-full \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libgomp \
    libstdc++ \
    libwebp-dev \
    libxml2-dev \
    libzip-dev \
    make \
    oniguruma-dev \
    postgresql-dev \
    sqlite-dev \
    unzip \
    wget \
    zip

RUN curl -fsSL https://github.com/yt-dlp/yt-dlp/releases/download/2026.07.04/yt-dlp_musllinux \
        -o /usr/local/bin/yt-dlp \
    && echo 'f7439ec2e3ffe69e06ac233f83f0d9687b89105939129bddcbf74e5de0f2b40e  /usr/local/bin/yt-dlp' | sha256sum -c - \
    && chmod a+rx /usr/local/bin/yt-dlp

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    ctype \
    gd \
    intl \
    mbstring \
    opcache \
    pdo \
    pdo_pgsql \
    pdo_sqlite \
    xml \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY custom-www.conf /usr/local/etc/php-fpm.d/www.conf
COPY src/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]

FROM base AS dev

RUN apk add --no-cache \
    nodejs \
    npm \
    pkgconfig

FROM node:24-alpine AS frontend

WORKDIR /app
COPY src/package*.json ./
RUN npm ci
COPY src/ .
RUN npm run build

# ---- whisper.cpp + model (content-aware chapter generation) ----
# Built in its own stage so only the binary + model land in the app image.
FROM alpine:3.20 AS whisper

ARG WHISPER_VERSION=v1.7.4
ARG WHISPER_MODEL=ggml-small.en.bin

RUN apk add --no-cache git build-base curl cmake

WORKDIR /build
# whisper.cpp builds with cmake; the CLI binary lands somewhere under build/.
# Locate it and copy to a known path so the app stage can COPY it reliably.
RUN git clone --depth 1 --branch ${WHISPER_VERSION} https://github.com/ggerganov/whisper.cpp.git . \
    && cmake -B build -DWHISPER_BUILD_TESTS=OFF -DWHISPER_BUILD_EXAMPLES=ON -DBUILD_SHARED_LIBS=OFF \
    && cmake --build build -j"$(nproc)" \
    && cp "$(find build -type f -name 'whisper-cli' | head -1)" /usr/local/bin/whisper-cli \
    && test -x /usr/local/bin/whisper-cli

RUN mkdir -p /models \
    && curl -fsSL "https://huggingface.co/ggerganov/whisper.cpp/resolve/main/${WHISPER_MODEL}" -o "/models/${WHISPER_MODEL}"

FROM base AS app

COPY src/ .
COPY --from=frontend /app/public/build /var/www/html/public/build
COPY --from=whisper /usr/local/bin/whisper-cli /usr/local/bin/whisper-cli
COPY --from=whisper /models/ggml-small.en.bin /opt/whisper-models/ggml-small.en.bin

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p storage/app/public/temp-youtube \
    && mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/testing \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p database \
    && chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/database \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/database

RUN apk add --no-cache su-exec

FROM nginx:1.31-alpine AS web

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
