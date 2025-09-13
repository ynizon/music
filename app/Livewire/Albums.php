<?php

namespace App\Livewire;

use Livewire\Component;

class Albums extends Component
{
    public $artist;

    public function mount($artist)
    {
        $this->artist = $artist;
    }

    public function render()
    {
        return view('livewire.albums');
    }
}
