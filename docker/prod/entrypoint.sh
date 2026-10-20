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

# Les dossiers du NAS peuvent appartenir à un autre utilisateur
mkdir -p storage/app/download storage/framework/cache storage/framework/sessions \
    storage/framework/views storage/logs bootstrap/cache database public/images/cache
for dir in storage bootstrap/cache database public/images/cache; do
    find "$dir" ! -user www-data -exec chown www-data:www-data {} +
done
# Sitemaps écrits à la racine de public
find public -maxdepth 1 ! -user www-data -exec chown www-data:www-data {} +

[ -f bootstrap/cache/packages.php ] || su -s /bin/sh www-data -c "php artisan package:discover"

if [ "$RUN_MIGRATIONS" = "true" ] && [ -f .env ]; then
    su -s /bin/sh www-data -c "php artisan migrate --force" || echo "Migrations en échec, voir les logs ci-dessus"
fi

exec docker-php-entrypoint "$@"
