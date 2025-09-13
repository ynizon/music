<table class="mytable myborder table table-striped" width="100%">
    @php
        /** @var App\Models\Artist $artist */
        $bSimilar = false;
        if (count($artist->getSimilar()) > 4){
            $bSimilar = true;
        }
    @endphp
    <thead>
    <tr>
        <th colspan="2">
            <h2>Artistes similaires <span class="inv">à {{$artist->getName()}}</span></h2>
            <div id="artistes_query" class="myquery"></div>
        </th>
    </tr>
    </thead>
    <tbody id="artistes">
        @if (count($artist->getSimilar()) > 0)
            @foreach ($artist->getSimilar() as $similar)
                <tr id="tr_{{ $loop->index}}" style="<?php if($loop->index>4){echo 'display:none';$bSimilar = true;}?>"  class="diapotr">
                    <td class="tc tcimage" width="120">
                        <a class="inv" href="/artist/{{ urlencode(str_replace("/","",$similar['name']))}}">
                                {{ $similar['name']}}</a>
                        <a class="cover" onclick="lookForArtist('{{ str_replace("'","\\'",$similar['name'])}}');">
                            <img height="90" width="120" @if ($loop->index>4) data- @endif
                            src="{{$similar['image']}}" alt="image"/>
                        </a>
                    </td>
                    <td>
                        <a style="display:none" onclick="lookForArtist('{{ str_replace("'","\\'",$similar['name'])}}');">{!!
                        $similar['name']!!}</a>
                        <a class="cursor" onclick="lookForArtist('{{ str_replace("'","\\'",$similar['name'])}}');">{!!
                        $similar['name']!!}</a>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td>&nbsp;</td>
                <td>Information non disponible</td>
            </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" class="tc bordertop">
                <br class="removemobile" />
                <img title="Utilisez les flèches du clavier pour aller plus vite" class="cursor "
                     alt="Précédent"
                     style="visibility:hidden;" src="/images/skip-backward-icon.png" onclick="pagination(-5,'tr');" id="prevtr">
                <img title="Utilisez les flèches du clavier pour aller plus vite" class="cursor "
                     alt="Suivant"
                     style="visibility: @if ($bSimilar) visible @else hidden @endif" src="/images/skip-forward-icon.png" onclick="pagination(5,'tr');"
                     id="nexttr">
            </td>
        </tr>
    </tfoot>
</table>
