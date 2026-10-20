#!/bin/bash
# Télécharge une playlist Spotify avec spotdl (sur le Docker de l'hôte) puis génère le .m3u
# $1 : dossier de destination (même chemin sur l'hôte et dans le conteneur)
# $2 : URL Spotify
set -x
DOCKER=$(command -v docker || echo /share/CACHEDEV1_DATA/.qpkg/container-station/bin/docker)

COOKIES=/share/nas/music/cookies.txt
COOKIE_VOLUME=()
COOKIE_OPTION=()
if [ -f "$COOKIES" ]; then
	COOKIE_VOLUME=(-v "$COOKIES":/cookies.txt:ro)
	COOKIE_OPTION=(--cookie-file /cookies.txt)
fi

"$DOCKER" run --rm -v "$1":/music "${COOKIE_VOLUME[@]}" spotdl/spotify-downloader download $2 --lyrics "${COOKIE_OPTION[@]}" --output "{track-number} - {artist} - {title}"

# spotdl tourne en root : on rend le dossier et les fichiers modifiables par tous
"$DOCKER" run --rm -v "$1":/music --entrypoint chmod spotdl/spotify-downloader -R ugo+w /music

current_name=$(basename "$1")
m3u_file="$1/$current_name.m3u"

# Supprime le fichier existant s'il y en a un pour éviter d'ajouter des lignes
if [ -f "$m3u_file" ]; then
    rm "$m3u_file"
fi

NB_FICHIERS=$(find "$1" -maxdepth 1 -type f ! -name ".*" -print |wc -l)
if [ "$NB_FICHIERS" -gt 0 ]; then

	# Écrit l'en-tête du fichier .m3u
	echo "#EXTM3U" >> "$m3u_file"

	# Liste les fichiers musicaux dans l'ordre alphabétique (chemins relatifs)
	find "$1" -maxdepth 1 -type f -regex ".*\.\(mp3\|flac\|wav\|m4a\|ogg\|wma\)$" -print0 | sort -z | while IFS= read -r -d $'\0' file; do
		echo "$(basename "$file")" >> "$m3u_file"
	done

	chmod ugo+w "$m3u_file"
	echo "Playlist créée avec succès : $m3u_file"
fi
