<table class="mytable myborder table table-striped" width="100%">
	<thead>
	<tr>
		<th colspan="2">
			<h2>Vidéos des concerts <span class="inv">de {{$artist->getName()}}</span></h2>
			<input title="Vous pouvez faire votre propre recherche ici" type="text" id="lives_youtube_query"
				   value="{{$artist->getName()}} live" class="myquery" onkeypress="if(event.keyCode === 13){searchFor('lives_youtube',this);}">
		</th>
	</tr>
	</thead>
	<tbody id="lives_youtube">
		@php
		//Albums Youtube
		$bYoutubeLive = false;
        if (isset($artist->youtube_live)){
            $tab = $artist->getYoutubelive();
            if (count($tab)> 4){
				$bYoutubeLive = true;
            }
        }
        $loopIndex = -1;
		@endphp
		@if (isset($artist->youtube_live))
			@foreach ($tab as $video)
				@php
                    $bOk = false;
                    switch ($video['kind']){
                        case "youtube#playlist":
                            $bOk = false;
                            if (isset($video['thumbnails'])){
                                $sVideoId = str_replace("/default.jpg","",str_replace("https://i.ytimg.com/vi/","",$video->snippet->thumbnails->default->url))."?list=".$video->id->playlistId;
                                $bOk = true;
                            }
                            break;
                        case "youtube#video":
                            $sVideoId = $video['videoId'];
                            $bOk = true;
                            break;
                    }
                    if ($bOk){
                        $loopIndex++;
                    }
				@endphp
				@if ($bOk)
					<tr id="tryl_{{$loopIndex }}" style="@if($loopIndex>4) display:none @endif" class="diapotryl">
						<td class="tc tcimage" width="120">
							<span class="cover" onclick="loadVideo('{{ ($sVideoId)}}','{{ urlencode($video['title'])}}');">
								<img @if ($loopIndex>4) data- @endif src="{{$video['thumbnail']}}" alt="image"/>
							</span>
						</td>
						<td>
							<span class="pointer" onclick="loadVideo('{{ ($sVideoId)}}','{{ urlencode($video['title'])}}');">
								{!! $video['title'] !!}
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
			<img title="Utilisez les flèches du clavier pour aller plus vite"
				 class="cursor " style="visibility:hidden;" src="/images/skip-backward-icon.png"
				 onclick="pagination(-5,'tryl');" id="prevtryl"
				 alt="Utilisez les flèches du clavier pour aller plus vite"/>
			<img title="Utilisez les flèches du clavier pour aller plus vite"
				 class="cursor " style="visibility:@if ($bYoutubeLive) visible @else hidden @endif;"
				 src="/images/skip-forward-icon.png" onclick="pagination(5,'tryl');"
				 id="nexttryl" alt="Utilisez les flèches du clavier pour aller plus vite" />
		</td>
	</tr>
	</tfoot>
</table>