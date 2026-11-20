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

# Le code est dans l'image. Les données persistantes sont dans /data (volume du NAS),
# vers lequel pointent des liens : .env, storage, database/database.sqlite, public/images/cache.
mkdir -p /data/storage/app/download /data/storage/app/sitemaps \
    /data/storage/framework/cache /data/storage/framework/sessions /data/storage/framework/views \
    /data/storage/logs /data/database /data/public/images/cache

if [ ! -f /data/.env ]; then
    echo "ATTENTION : /data/.env introuvable (modèle : .env.example dans l'image)"
fi
# Base absente : on la crée vide, les migrations construisent le schéma
[ -f /data/database/database.sqlite ] || touch /data/database/database.sqlite

# Dossiers écrits par l'application, modifiables par le groupe du propriétaire de /data (ou PGID).
# Ces dossiers contiennent des millions de fichiers (caches d'images, sessions) : le parcours
# récursif complet prend des heures sur disque dur. Il n'est fait qu'une fois (fichier témoin),
# ensuite seul le dossier lui-même est vérifié (les fichiers créés par Apache sont déjà corrects ;
# même le premier niveau de public/images/cache compte environ un million de fichiers).
data_gid=${PGID:-$(stat -c %g /data)}
for dir in /data/storage /data/database /data/public/images/cache; do
    depth="-maxdepth 0"
    [ -f "$dir/.permissions-ok" ] || depth=""
    find "$dir" $depth \( ! -user www-data -o ! -group "$data_gid" \) -exec chown "www-data:$data_gid" {} +
    find "$dir" $depth ! -perm -g=w -exec chmod g+rwX {} +
    find "$dir" $depth -type d ! -perm -g=s -exec chmod g+s {} +
    touch "$dir/.permissions-ok" && chown "www-data:$data_gid" "$dir/.permissions-ok"
done
# La base SQLite crée ses fichiers -wal/-shm à côté d'elle
chown "www-data:$data_gid" /data/database/database.sqlite

# Fichiers créés par Apache modifiables par le groupe
umask 0002

# Les vues compilées (dans storage) peuvent dater d'une image précédente
su -s /bin/sh www-data -c "php artisan view:clear" || true

if [ "$RUN_MIGRATIONS" = "true" ] && [ -f /data/.env ]; then
    su -s /bin/sh www-data -c "php artisan migrate --force" || echo "Migrations en échec, voir les logs ci-dessus"
fi

# TRUSTED_PROXIES peut être défini dans .env (prioritaire sur la variable du conteneur) :
# Apache le lit dans son environnement (RemoteIPInternalProxy), pas Laravel.
env_proxies=$(grep -E '^TRUSTED_PROXIES=' /data/.env 2>/dev/null | tail -n 1 | cut -d= -f2- | tr -d "\"'\r")
if [ -n "$env_proxies" ]; then
    export TRUSTED_PROXIES="$env_proxies"
fi
echo "Proxys de confiance (X-Forwarded-For) : $TRUSTED_PROXIES"

exec docker-php-entrypoint "$@"
