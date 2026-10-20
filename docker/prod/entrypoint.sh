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

# Les volumes du NAS peuvent être vides ou appartenir à un autre utilisateur
mkdir -p storage/app/download storage/framework/cache storage/framework/sessions \
    storage/framework/views storage/logs public/images/cache /data/database
for dir in storage bootstrap/cache public/images/cache /data/database; do
    find "$dir" ! -user www-data -exec chown www-data:www-data {} +
done

if [ "$RUN_MIGRATIONS" = "true" ] && [ -f .env ]; then
    su -s /bin/sh www-data -c "php artisan migrate --force" || echo "Migrations en échec, voir les logs ci-dessus"
fi

exec docker-php-entrypoint "$@"
