<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spotdl extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist',
        'album',
        'path',
        'spotifyurl',
    ];

    public function getAlbum() : string
    {
        return $this->album;
    }

    public function setAlbum(string $album) : void
    {
        $this->album = $album;
    }

    public function getSpotifyurl() : string
    {
        return $this->spotifyurl;
    }

    public function setSpotifyurl(string $spotifyurl) : void
    {
        $this->spotifyurl = $spotifyurl;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function setPath(string $path) : void
    {
        $this->path = $path;
    }

    public function getArtist() : string
    {
        return $this->artist;
    }

    public function setArtist(string $artist) : void
    {
        $this->artist = $artist;
    }
}
