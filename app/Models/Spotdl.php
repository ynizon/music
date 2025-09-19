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
        'nbtracks',
        'todo',
        'done',
        'avoid',
    ];

    public function isDone() : bool
    {
        return $this->done;
    }

    public function setDone(bool $done) : void
    {
        $this->done = $done;
    }

    public function isAvoid() : bool
    {
        return $this->avoid;
    }

    public function setAvoid(bool $avoid) : void
    {
        $this->avoid = $avoid;
    }

    public function isTodo() : bool
    {
        return $this->todo;
    }

    public function setTodo(bool $todo) : void
    {
        $this->todo = $todo;
    }

    public function getAlbum() : string
    {
        return $this->album;
    }

    public function setAlbum(string $album) : void
    {
        $this->album = $album;
    }

    public function getNbtracks() : string
    {
        return $this->nbtracks;
    }

    public function setNbtracks(int $nbtracks) : void
    {
        $this->nbtracks = $nbtracks;
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
