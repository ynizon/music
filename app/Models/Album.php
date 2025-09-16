<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Album extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (isset($attributes['name'])){
            $this->setName(strtolower($attributes['name']));
        }
        if (isset($attributes['artist'])){
            $this->setArtist($attributes['artist']);
        }
        if (isset($attributes['slug'])){
            $this->setSlug($this->artist->getSlug() . "/".Str::slug($attributes['slug']));
        }
    }

    protected $fillable = [
        'name',
        'youtube',
        'info',
        'artist_id',
        'slug',
    ];

    public function getId() : string
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getYoutube() : array
    {
        return $this->youtube == null ? []  : json_decode($this->youtube, true);
    }

    public function setYoutube(array $youtube) : void
    {
        $this->youtube = json_encode($youtube);
    }

    public function getInfo() : string
    {
        return $this->info;
    }

    public function setInfo(string $info) : void
    {
        $this->info = $info;
    }

    public function getSlug() : string
    {
        return $this->slug;
    }

    public function setSlug(string $slug) : void
    {
        $this->slug = $slug;
    }

    public function setArtistid(int $artistId) : void
    {
        $this->artist_id = $artistId;
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function setArtist(Artist $artist)
    {
        $this->artist_id = $artist->getId();
    }

    public function refreshData(){
        $bRefresh = false;
        $bSave = false;

        if ($this->getName() != "-"){
            //Detecter si les infos sont obsoletes
            if (isset($this->updated_at)){
                $date1 = strtotime($this->updated_at);
                $date2 = strtotime(date("Y-m-d"));
                $nbJoursTimestamp = $date2 - $date1;
                $nbJours = round($nbJoursTimestamp/86400,0); // 86 400 = 60*60*24

                if ($nbJours>360){
                    $bRefresh = true;
                }
            }else{
                $bRefresh = true;
            }

            if ($bRefresh or !isset($this->youtube)){
                $bSave = $this->refreshYoutube($bSave);
            }

            if ($bRefresh or ! $this->getInfo() !== null){
                $bSave = $this->refreshInfo($bSave);
            }

            if ($bSave){
                $this->save();
            }
        }
    }

    private function refreshYoutube($bSave = false) : bool{
        try{
            $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=".
                urlencode($this->artist->getName()." ".$this->getName()).
                "&maxResults=50&key=".env("YOUTUBE_API");
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

                $this->setYoutube($albums);
                $bSave = true;
            }
        }catch(\Exception $e){

        }

        return $bSave;
    }

    private function refreshInfo($bSave = false) : bool{
        $sBio = file_get_contents("http://ws.audioscrobbler.com/2.0/?method=album.getinfo&artist=".
                                  urlencode($this->artist->getName())."&lang=fr&album=".
                                  urlencode($this->getName())."&format=json&api_key=".config("lastfm.api_key"));

        if (trim($sBio) != ""){
            $this->setInfo($sBio);
            $bSave = true;
        }
        return $bSave;
    }
}
