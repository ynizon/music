<?php
//Albums Youtube
$bYoutubeLive = false;

if (isset($artist->youtube_live)){
	$tab = $artist->getYoutubelive();
	$i=0;
	foreach ($tab as $video){
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
			?>
			<tr id="tryl_{{ $i}}" style="<?php if($i>4){echo "display:none";$bYoutubeLive = true;}?>"
				class="diapotryl">
				<td class="tc tcimage" width="120">
					<span class="cover" onclick="loadVideo('{{ ($sVideoId)}}','{{ urlencode
					($video['title'])}}');">
						<img <?php if ($i>4){echo "data-";} ?>src="{{$video['thumbnail']}}" />
					</span>
				</td>
				<td>
					<span class="pointer" onclick="loadVideo('{{ ($sVideoId)}}','{{ urlencode
					($video['title'])}}');">
						{{ $video['title']}}
					</span>
				</td>
			</tr>
			<?php
			$i++;
		}
	}
}
?>									
<script>
	$( document ).ready(function() {
		$("#block_lives_youtube").css('visibility','visible');
		$("#nexttryl").css('visibility','hidden');
		@if ($bYoutubeLive)
			$("#nexttryl").css('visibility','visible');
        @endif
	});
</script>