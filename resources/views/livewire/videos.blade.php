@php
	/** @var App\Models\Title $title */
	/** @var App\Models\Artist $artist */
	/** @var App\Models\Album $album */

	//Albums Youtube
    if (isset($artist)){
        $tab = $artist->getYoutubefullalbum();
    }

    if (isset($album)){
        $tab = $album->getYoutube();
        $artist = $album->artist;
    }

    if (isset($title)){
        $tab = $title->getYoutube();
        $artist = $album->artist;
    }

    $bYoutubealbum = false;
@endphp

<table class="mytable myborder table table-striped" width="100%">
	<thead>
	<tr>
		<th colspan="2">
			<h2>Vidéos des albums <span class="inv">de {{ $artist->name }}</span></h2>
			<input title="Vous pouvez faire votre propre recherche ici" type="text" id="albums_youtube_query" value="<?php echo $artist->name;?> album" class="myquery" onkeypress="if(event.keyCode == 13){searchFor('albums_youtube',this);}">
		</th>
	</tr>
	</thead>
	<tbody id="albums_youtube">
	@if (isset($tab))
		@foreach ($tab as $video)
			@php
				if (count($tab) > 4){
                    $bYoutubealbum = true;
                }
				$bOk = false;

				switch ($video['kind']){
					case "youtube#playlist":
						$bOk = false;
						if (isset($video['thumbnail'])){
							$sVideoId = str_replace("/default.jpg","",str_replace("https://i.ytimg.com/vi/","",
									 $video['thumbnail'])
									)."?list=".$video['playlistId'];
							$bOk = true;
						}

						break;
					case "youtube#video":
						$sVideoId = $video['videoId'];
						$bOk = true;
						break;
				}
			@endphp
			@if ($bOk)
				<tr id="trya_{{$loop->index}}" style="@if($loop->index>4) display:none @endif"
					class="diapotrya">
					<td class="tc tcimage" width="120">
						<span class="cover" onclick="loadVideo('{{ $sVideoId }}','{{ urlencode($video['title'])}}');">
							<img @if ($loop->index>4) data- @endif src="{{$video['thumbnail']}}" alt="" />
						</span>
					</td>
					<td>
						<span class="pointer" onclick="loadVideo('{{ ($sVideoId )}}','{{ urlencode($video['title'])}}');">
							{!! $video['title']!!}
						</span>
					</td>
				</tr>
			@endif
		@endforeach
	@endif

	</tbody>
	<tfoot>
	<tr>
		<td colspan="2" class="tc bordertop">
			<br class="removemobile"/>
			<img title="Utilisez les flèches du clavier pour aller plus vite" class="cursor "
				 style="visibility:hidden;" src="/images/skip-backward-icon.png" onclick="pagination(-5,'trya');"
				 id="prevtrya" alt="Précédent"/>
			<img title="Utilisez les flèches du clavier pour aller plus vite" class="cursor "
				 style="visibility:@if ($bYoutubealbum) visible @else hidden @endif"
				 src="/images/skip-forward-icon.png"
				 onclick="pagination(5,'trya');"
				 id="nexttrya" alt="Suivant"/>
		</td>
	</tr>
	</tfoot>
</table>