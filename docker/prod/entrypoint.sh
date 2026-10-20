#!/bin/sh
set -e
cd /var/www/html

# Accès au socket Docker de l'hôte pour www-data (téléchargements spotdl)
if [ -S /var/run/docker.sock ]; then
    sock_gid=$(stat -c %g /var/run/docker.sock)
    sock_group=$(getent group "$sock_gid" | cut -d: -f1)
    if [ -z "$sock_group" ]; then
        groupadd -g "$sock_gid" dockerhost
        sock_group=dockerhost
    fi
    usermod -aG "$sock_group" www-data
fi

# Le site (code + données) est dans /var/www/html, monté depuis le NAS.
# Premier démarrage : on y copie le code de l'image sans écraser .env, storage, database...
if [ ! -f artisan ]; then
    echo "Code absent : copie du code de l'image dans /var/www/html"
    cp -an /opt/app/. /var/www/html/ || true
fi
[ -f .env ] || echo "ATTENTION : /var/www/html/.env introuvable"

# vendor absent ou composer.lock modifié : réinstallation des dépendances
if [ ! -f vendor/composer/installed.json ] || [ composer.lock -nt vendor/composer/installed.json ]; then
    composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
    touch vendor/composer/installed.json
    rm -f bootstrap/cache/packages.php bootstrap/cache/services.php
fi

mkdir -p storage/app/download storage/framework/cache storage/framework/sessions \
    storage/framework/views storage/logs bootstrap/cache database public/images/cache

# Code modifiable depuis le NAS : il appartient au propriétaire du dossier monté (ou PUID/PGID)
code_uid=${PUID:-$(stat -c %u /var/www/html)}
code_gid=${PGID:-$(stat -c %g /var/www/html)}
find . \( -path ./storage -o -path ./bootstrap/cache -o -path ./database -o -path ./public/images/cache \
    -o -path ./vendor -o -path './public/sitemap*' \) -prune -o ! -user "$code_uid" -exec chown "$code_uid:$code_gid" {} +

# Dossiers écrits par l'application, restant modifiables par le groupe du propriétaire
for dir in storage bootstrap/cache database public/images/cache; do
    find "$dir" \( ! -user www-data -o ! -group "$code_gid" \) -exec chown "www-data:$code_gid" {} +
    find "$dir" ! -perm -g=w -exec chmod g+rwX {} +
    find "$dir" -type d ! -perm -g=s -exec chmod g+s {} +
done
# Sitemaps écrits à la racine de public
chgrp www-data public && chmod g+w public
find public -maxdepth 1 -name 'sitemap*' ! -user www-data -exec chown "www-data:$code_gid" {} +

# Fichiers créés par Apache modifiables par le groupe
umask 0002

[ -f bootstrap/cache/packages.php ] || su -s /bin/sh www-data -c "php artisan package:discover"

if [ "$RUN_MIGRATIONS" = "true" ] && [ -f .env ]; then
    su -s /bin/sh www-data -c "php artisan migrate --force" || echo "Migrations en échec, voir les logs ci-dessus"
fi

exec docker-php-entrypoint "$@"
