<?php

$bSimilar = false;

?>
@if (count($artist->getSimilar()) > 0)
    @foreach ($artist->getSimilar() as $similar)
        <tr id="tr_{{ $loop->index}}" style="<?php if($loop->index>4){echo 'display:none';$bSimilar = true;}?>"  class="diapotr">
            <td class="tc tcimage" width="120">
                <a class="inv" href="/artist/{{ urlencode(str_replace("/","",$similar['name']))}}">
                        {{ $similar['name']}}</a>
                <a class="cover" onclick="lookForArtist('{{ str_replace("'","\\'",$similar['name'])}}');">
                    <img height="90" width="120" <?php if ($loop->index>4){echo "data-";} ?>src="{{ $similar['image']}}"
                    />
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

<script>
	$( document ).ready(function() {
		$("#block_artistes").css('visibility','visible');
		$("#nexttr").css('visibility','hidden');
		@if ($bSimilar)
			$("#nexttr").css('visibility','visible');
        @endif
	});
</script>
