@php

//Albums Youtube
if (isset($artist)){
	$tab = $artist->getYoutubefullalbum();
}

if (isset($album)){
	$tab = $album->getYoutube();
}

if (isset($title)){
	$tab = $title->getYoutube();
}

$bYoutubealbum = false;
@endphp
@if (isset($tab))
	@foreach ($tab as $video)
		@php
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
			<tr id="trya_{{$loop->index}}" style="@php if($loop->index>4){echo 'display:none';$bYoutubealbum = true;}@endphp"
				class="diapotrya">
				<td class="tc tcimage" width="120">
					<span class="cover" onclick="loadVideo('{{ $sVideoId }}','{{ urlencode
					($video['title'])}}');">
						<img @php if ($loop->index>4){echo "data-";} @endphp src="{{$video['thumbnail']}}" />
					</span>
				</td>
				<td>
					<span class="pointer" onclick="loadVideo('{{ ($sVideoId )}}','{{ urlencode
					($video['title'])}}');">
						{!! $video['title']!!}
					</span>
				</td>
			</tr>
		@endif
	@endforeach
@endif

<script>
$( document ).ready(function() {
	$("#block_albums_youtube").css('visibility','visible');
	$("#nexttrya").css('visibility','hidden');
	@if ($bYoutubealbum)
		$("#nexttrya").css('visibility','visible');
	@endif
});
</script>