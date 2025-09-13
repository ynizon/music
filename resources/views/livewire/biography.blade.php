<div>
    @if ($artist->getBiography() != '')
        <div itemscope itemtype="http://schema.org/MusicGroup">
            <h3 itemprop="name">{{ $artist->getName()}}</h3>
            <span itemprop="description">
                {!! $artist->getBiography() !!}
            </span>
            <br/>
            <a target="_blank" href="http://fr.wikipedia.org/wiki/{{ str_replace('"',"'", $artist->getName())}}">
                Voir la fiche wikipédia.</a>
        </div>
    @else
        Biographie non disponible
    @endif
</div>