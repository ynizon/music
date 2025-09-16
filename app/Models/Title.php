<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Title extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (isset($attributes['name'])){
            $this->setName(strtolower($attributes['name']));
        }
        if (isset($attributes['album'])){
            $this->setAlbum($attributes['album']);
        }
        if (isset($attributes['artist'])){
            $this->setArtist($attributes['artist']);
        }
        if (isset($attributes['slug'])){
            $this->setSlug($this->album->getSlug() . "/".Str::slug($attributes['slug']));
        }
    }

    protected $fillable = [
        'name',
        'youtube',
        'album_id',
        'artist_id',
        'slug',
    ];

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getSlug() : string
    {
        return $this->slug;
    }

    public function setSlug(string $slug) : void
    {
        $this->slug = $slug;
    }

    public function getYoutube() : array
    {
        return $this->youtube == null ? []  : json_decode($this->youtube, true);
    }

    public function setYoutube(array $youtube) : void
    {
        $this->youtube = json_encode($youtube);
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function setArtist(Artist $artist)
    {
        $this->artist_id = $artist->getId();
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function setAlbum(Album $album)
    {
        $this->album_id = $album->getId();
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

            if ($nbJours>360){
                $bRefresh = true;
            }
        }else{
            $bRefresh = true;
        }

        if ($bRefresh or !isset($this->youtube)){
            $bSave = $this->refreshYoutubeData($bSave);
        }

        if ($bSave){
            $this->save();
        }
    }

    private function refreshYoutubeData(bool $bSave) : bool{
        try {
            $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" .
                urlencode($this->artist->getName() . " " . $this->getName()) .
                "&maxResults=50&key=" . env("YOUTUBE_API");
            $sBio = Helpers::getYoutubeData($url);

            if ($sBio != null && trim($sBio) != "") {
                $sBio = json_decode($sBio, true);

                $titles = [];
                foreach ($sBio["items"] as $title) {
                    $titles[] = [
                        "title" => $title["snippet"]["title"],
                        "kind" => $title["id"]["kind"],
                        "playlistId" => $title["id"]["playlistId"] ?? "",
                        "videoId" => $title["id"]["videoId"] ?? "",
                        "thumbnail" => $title["snippet"]["thumbnails"]["default"]["url"] ?? "/images/default_rotate.png"
                    ];
                }

                $this->setYoutube($titles);
                $bSave = true;
            }
        }catch(\Exception $e){

        }
        return $bSave;
    }
}
