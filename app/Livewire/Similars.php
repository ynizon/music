<?php

namespace App\Livewire;

use Livewire\Component;

class Similars extends Component
{
    public $artist;

    public function mount($artist)
    {
        $this->artist = $artist;
    }

    public function render()
    {
        return view('livewire.similars');
    }
}
