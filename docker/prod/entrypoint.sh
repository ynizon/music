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

# Données du site dans /data (dossier du NAS) : .env, storage, database, public/images/cache
mkdir -p /data/storage/app/download /data/storage/framework/cache /data/storage/framework/sessions \
    /data/storage/framework/views /data/storage/logs /data/database /data/public/images/cache
[ -f /data/.env ] || echo "ATTENTION : /data/.env introuvable"
ln -sfn /data/.env .env
rm -rf storage public/images/cache
ln -s /data/storage storage
ln -s /data/public/images/cache public/images/cache

# Les dossiers du NAS peuvent appartenir à un autre utilisateur
for dir in /data/storage /data/database /data/public/images/cache bootstrap/cache; do
    find "$dir" ! -user www-data -exec chown www-data:www-data {} +
done

if [ "$RUN_MIGRATIONS" = "true" ] && [ -f .env ]; then
    su -s /bin/sh www-data -c "php artisan migrate --force" || echo "Migrations en échec, voir les logs ci-dessus"
fi

exec docker-php-entrypoint "$@"
