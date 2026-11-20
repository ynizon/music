FROM php:8.6-apache

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2.9 /usr/bin/composer /usr/bin/composer
# Client Docker : les téléchargements spotdl sont lancés sur le Docker de l'hôte (socket monté)
COPY --from=docker:27.5.1-cli /usr/local/bin/docker /usr/local/bin/docker

# Extensions PHP, ffmpeg + yt-dlp (HomeController::download), modules Apache
RUN apt-get update \
    && apt-get install -y --no-install-recommends ffmpeg unzip \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions intl zip bcmath gd exif sockets opcache \
    && a2enmod rewrite remoteip headers \
    && mkdir -p /opt/ffmpeg && ln -s /usr/bin/ffmpeg /opt/ffmpeg/ffmpeg \
    && case "$(uname -m)" in aarch64) YTDLP=yt-dlp_linux_aarch64 ;; *) YTDLP=yt-dlp_linux ;; esac \
    && curl -fsSL -o /usr/local/bin/yt-dlp_linux "https://github.com/yt-dlp/yt-dlp/releases/latest/download/$YTDLP" \
    && chmod +x /usr/local/bin/yt-dlp_linux

COPY docker/prod/php.ini $PHP_INI_DIR/conf.d/zz-app.ini
COPY docker/prod/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/prod/entrypoint.sh /usr/local/bin/app-entrypoint
COPY docker/prod/download_spotify.sh /usr/local/bin/download_spotify.sh
RUN chmod +x /usr/local/bin/app-entrypoint /usr/local/bin/download_spotify.sh

ENV TRUSTED_PROXIES="10.0.3.0/24 172.16.0.0/12 127.0.0.1" \
    RUN_MIGRATIONS=true \
    OPCACHE_VALIDATE_TIMESTAMPS=0 \
    COMPOSER_HOME=/tmp/composer \
    COMPOSER_ALLOW_SUPERUSER=1

# Le code est entièrement dans l'image : une nouvelle image = un nouveau code.
# Les données persistantes sont dans le volume /data (voir docker-compose.yml) :
#   /data/.env
#   /data/database/database.sqlite
#   /data/storage/
#   /data/public/images/cache/
WORKDIR /var/www/html

# Dépendances d'abord pour profiter du cache de build
COPY composer.json composer.lock ./
# Paquets locaux (dépôt Composer de type "path")
COPY packages ./packages
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
# storage est exclu du contexte : Laravel a besoin de son arborescence pour package:discover.
# Ensuite, les données sont remplacées par des liens vers /data.
RUN mkdir -p storage/app storage/framework/cache storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache public/images \
    && composer dump-autoload --optimize --no-dev --no-scripts \
    && php artisan package:discover --ansi \
    && rm -rf storage public/images/cache database/database.sqlite .env \
    && ln -s /data/.env .env \
    && ln -s /data/storage storage \
    && ln -s /data/database/database.sqlite database/database.sqlite \
    && ln -s /data/public/images/cache public/images/cache \
    && chown -R www-data:www-data bootstrap/cache

VOLUME /data

ENTRYPOINT ["app-entrypoint"]
CMD ["apache2-foreground"]
