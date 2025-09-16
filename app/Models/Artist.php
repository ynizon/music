<?php

namespace App\Models;

use Aerni\Spotify\Facades\Spotify;
use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Artist extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (isset($attributes['name'])){
            $this->setName(strtolower($attributes['name']));
        }
        if (isset($attributes['slug'])){
            $this->setSlug(Str::slug($attributes['slug']));
        }
    }

    use HasFactory;
    protected $fillable = [
        'name',
        'biography',
        'lang',
        'slug',
        'similar',
        'topalbums',
        'youtube_full_album',
        'youtube_albums',
        'youtube_live',
        'spotifyid',
        'spotify_albums',
    ];

    public function getAlbum($name)
    {
        return Album::where("artist_id","=",$this->id)->where('name', 'like', '%' . $name . '%')->first();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAlbums()
    {
        return $this->hasMany(Album::class);
    }

    public function getBiography() : string
    {
        return str_replace("User-contributed text is available under the Creative Commons By-SA License; additional
			terms may apply.","",str_replace("\n","<br/>",str_replace("Read more on ","En savoir plus sur ",
                                                                      str_replace("User-contributed text is available under the Creative Commons By-SA License and may also be
			available under the GNU FDL.","",str_replace("<a ","<a target='_blank' ", $this->biography)))));
    }

    public function setBiography(string $biography) : void
    {
        $this->biography = $biography;
    }

    public function getSlug() : string
    {
        return $this->slug;
    }

    public function setSlug(string $slug) : void
    {
        $this->slug = $slug;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getLang() : string
    {
        return $this->lang;
    }

    public function setLang(string $lang) : void
    {
        $this->lang = $lang;
    }

    public function getSimilar() : array
    {
        return $this->similar == null ? []  : json_decode($this->similar, true);
    }

    public function setSimilar(array $similar) : void
    {
        $this->similar = json_encode($similar);
    }

    public function getTopalbums() : array
    {
        return $this->topalbums == null ? []  : json_decode($this->topalbums, true);
    }

    public function setTopalbums(array $topalbums) : void
    {
        $this->topalbums = json_encode($topalbums);
    }

    public function getYoutubeFullAlbum() : array
    {
        return $this->youtube_full_album == null ? []  : json_decode($this->youtube_full_album, true);
    }

    public function setYoutubeFullAlbum(array $youtube_full_album) : void
    {
        $this->youtube_full_album = json_encode($youtube_full_album);
    }

    public function getYoutubeLive() : array
    {
        return $this->youtube_live == null ? []  : json_decode($this->youtube_live, true);
    }

    public function setYoutubeLive(array $youtube_live) : void
    {
        $this->youtube_live = json_encode($youtube_live);
    }

    public function getSpotifyAlbums() : array
    {
        return $this->spotify_albums == null ? []  : json_decode($this->spotify_albums, true);
    }

    public function setSpotifyAlbums(string $spotify_albums) : void
    {
        $this->spotify_albums = $spotify_albums;
    }

    public function getSpotifyid() : string
    {
        return $this->spotifyid;
    }

    public function setSpotifyid(string $spotifyid) : void
    {
        $this->spotifyid = $spotifyid;
    }


    public function refreshData(){
        $bRefresh = false;
        $bSave = false;

        //Detecter si les infos sont obsoletes
        if (isset($this->updated_at)){
            $date1 = strtotime($this->updated_at);
            $date2 = strtotime(date("Y-m-d"));
            $nbJoursTimestamp = $date2 - $date1;
            $nbJours = round($nbJoursTimestamp/86400,0); // 86 400 = 60*60*24

            if ($nbJours>env("DELAY_CACHE")){
                $bRefresh = true;
            }
        }else{
            $bRefresh = true;
        }

        if ($bRefresh or $this->getBiography() == NULL) {
            $bSave = $this->refreshBio($bSave);
        }

        if ($bRefresh or $this->getSimilar() == NULL){
            $bSave = $this->refreshSimilars($bSave);
        }

        if ($bRefresh or $this->getTopalbums() == NULL){
            $bSave = $this->refreshTopAlbums();
        }

        if ($bRefresh or $this->getYoutubeFullAlbum() == NULL){
            $bSave = $this->refreshYoutubeFullAlbums($bSave);
        }

        if ($bRefresh or $this->getYoutubeLive() == NULL){
            $bSave = $this->refreshYoutubeLives($bSave);
        }

        if ($bRefresh or $this->getSpotifyAlbums() == NULL) {
            $bSave = $this->refreshSpotifyAlbums($bSave);
        }
        if ($bSave){
            $this->save();
        }
    }

    private function refreshSpotifyAlbums($bSave = false) : bool{
        try {
            $items = Spotify::searchItems($this->getName(), 'artist')->get();
            $albumsSpotify = [];
            if (count($items) > 0) {
                $spotifyArtistId = $items['artists']['items'][0]['id'];
                $this->setSpotifyid($spotifyArtistId);
                $albums = Spotify::artistAlbums($spotifyArtistId)->get();
                foreach ($albums['items'] as $album) {
                    $albumsSpotify[strtolower($album['name'])] = $album['external_urls']['spotify'];
                }
                $bSave = true;
            }
            $this->setSpotifyAlbums(json_encode($albumsSpotify));
        }catch (\Exception $e){
            Log::error($e->getMessage());
        }
        return $bSave;
    }

    private function refreshBio($bSave = false) : bool{
        try {
            $url = "http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&artist="
                . urlencode($this->getName()) . "&lang=fr&format=json&api_key="
                . config("lastfm.api_key");
            $sBio = file_get_contents($url);

            $sBioFR = "";
            if (trim($sBio) != "") {
                $sBio = json_decode($sBio, true);
                $this->setBiography($sBio["artist"]["bio"]["content"]);
                $this->setName($sBio["artist"]['name']);
                $this->setLang("fr");
                $bSave = true;
                $sBioFR = $sBio;
            }

            //La bio est vide, alors la syntaxe est mauvaise, on prend donc la version UK
            // pour avoir la bonne syntaxe
            if ($sBioFR == "") {
                $sBio = file_get_contents(
                    "http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&artist="
                    . urlencode($this->getName()) . "&format=json&api_key=" . config("lastfm.api_key")
                );
                if ($sBio != null && trim($sBio) != "") {
                    $sBio = json_decode($sBio, true);
                    $this->setBiography($sBio["artist"]["bio"]["content"]);
                    $this->setName($sBio["artist"]['name']);
                    $this->setLang("uk");
                    $bSave = true;
                }
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }

        return $bSave;
    }

    private function refreshSimilars($bSave = false) : bool {
        try {
            $sBio = file_get_contents(
                "http://ws.audioscrobbler.com/2.0/?method=artist.getsimilar&artist=" .
                urlencode($this->getName()) . "&lang=fr&format=json&api_key=" . config("lastfm.api_key")
            );
            if ($sBio != null && trim($sBio) != "") {
                $sBio = json_decode($sBio, true);
                $similars = [];

                foreach ($sBio["similarartists"]["artist"] as $similar) {
                    $image = "/images/default_rotate.png";
                    if (isset($similar['image'][0]['#text'])) {
                        $image = $similar['image'][0]['#text'];
                    }
                    if (isset($similar['mbid'])) {
                        if ($similar['mbid'] != "") {
                            $image = "/picture/" . $similar['mbid'] . "?default=" . $image;
                        }
                    }
                    $similars[] = ["name" => $similar['name'], "image" => $image];
                }

                $this->setSimilar($similars);
                $bSave = true;
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
        return $bSave;
    }

    private function refreshTopAlbums($bSave = false) : bool {
        try {
            $sBio = file_get_contents(
                "http://ws.audioscrobbler.com/2.0/?method=artist.getTopAlbums&artist=" .
                urlencode($this->getName())
                . "&lang=fr&format=json&api_key=" . config("lastfm.api_key")
            );
            if ($sBio != null && trim($sBio) != "") {
                $sBio = json_decode($sBio, true);
                $topalbums = [];

                foreach ($sBio["topalbums"]["album"] as $album) {
                    $image = "/images/default_rotate.png";
                    if (isset($album['image'][0]['#text'])) {
                        $image = $album['image'][0]['#text'];
                    }
                    $topalbums[] = ["name" => $album['name'], "image" => $image];
                }
                $this->setTopalbums($topalbums);
                $bSave = true;
            }
        }catch (\Exception $e){
            Log::error($e->getMessage());
        }
        return $bSave;
    }

    private function refreshYoutubeFullAlbums($bSave = false) : bool {
        try {
            $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" .
                urlencode($this->getName() . " full album")
                . "&maxResults=50&key=" . env("YOUTUBE_API");
            $sBio = Helpers::getYoutubeData($url);
            if ($sBio != null && trim($sBio) != "") {
                $sBio = json_decode($sBio, true);

                $albums = [];
                foreach ($sBio["items"] as $album) {
                    $albums[] = [
                        "title" => $album["snippet"]["title"],
                        "kind" => $album["id"]["kind"],
                        "playlistId" => $album["id"]["playlistId"] ?? "",
                        "videoId" => $album["id"]["videoId"] ?? "",
                        "thumbnail" => $album["snippet"]["thumbnails"]["default"]["url"] ?? "/images/default_rotate.png"
                    ];
                }

                $this->setYoutubeFullAlbum($albums);
                $bSave = true;
            }
        }catch (\Exception $e){
            Log::error($e->getMessage());
        }
        return $bSave;
    }

    private function refreshYoutubeLives($bSave = false) : bool {
        try {
            $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" .
                urlencode($this->getName() . " live")
                . "&maxResults=50&key=" . env("YOUTUBE_API");
            $sBio = Helpers::getYoutubeData($url);

            if ($sBio != null && trim($sBio) != "") {
                $sBio = json_decode($sBio, true);

                $albums = [];
                foreach ($sBio["items"] as $album) {
                    $albums[] = [
                        "title" => $album["snippet"]["title"],
                        "kind" => $album["id"]["kind"],
                        "playlistId" => $album["id"]["playlistId"] ?? "",
                        "videoId" => $album["id"]["videoId"] ?? "",
                        "thumbnail" => $album["snippet"]["thumbnails"]["default"]["url"] ?? "/images/default_rotate.png"
                    ];
                }
                $this->setYoutubeLive($albums);
                $bSave = true;
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
        return $bSave;
    }
}
