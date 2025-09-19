#!/bin/bash
/share/CACHEDEV1_DATA/.qpkg/container-station/bin/docker run --rm -v "$1":/music spotdl/spotify-downloader download "$2" --lyrics --cookie-file /share/nas/music/cookies.txt  --output "{track-number} - {artist} - {title}"

current_name=$(basename "$1")
parent_name=$(basename "$(dirname "$1")")
m3u_file="$1/$current_name.m3u"

# Supprime le fichier existant s'il y en a un pour éviter d'ajouter des lignes
if [ -f "$m3u_file" ]; then
    rm "$m3u_file"
fi

# Écrit l'en-tête du fichier .m3u
echo "#EXTM3U" >> "$m3u_file"

# Liste les fichiers musicaux (mp3, m4a, etc.) dans l'ordre alphabétique
# et écrit leur chemin relatif dans le fichier .m3u
#
# Le 'sort' par défaut trie alphabétiquement.
# Le 'ls' inclut le chemin relatif, ce qui est parfait pour les playlists.
find "$1" -maxdepth 1 -type f -regex ".*\.\(mp3\|flac\|wav\|m4a\|ogg\|wma\)$" -print0 | sort -z | while IFS= read -r -d $'\0' file; do
    # Écrit le chemin relatif du fichier
    echo "$(basename "$file")" >> "$m3u_file"
done

echo "Playlist créée avec succès : $m3u_file"
chmod g+w "$1"