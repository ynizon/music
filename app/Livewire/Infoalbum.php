<?php

namespace App\Livewire;

use Livewire\Component;

class Infoalbum extends Component
{
    public $artist;
    public $album;

    public function mount($artist, $album)
    {
        $this->artist = $artist;
        $this->album = $album;
    }

    public function render()
    {
        return view('livewire.infoalbum');
    }
}
