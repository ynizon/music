<?php
//Liste albums
$i = 0;
$bAlbums = false;
if (count($artist->getTopalbums()) > 0){
	foreach ($artist->getTopalbums() as $album){
		if ($album['name'] != "(null)"){
		?>
			<tr id="tral_<?php echo $i;?>" style="<?php if($i>4){echo 'display:none;';$bAlbums = true;}?>" class="diapotral">
				<td width="61" class="tc tcimage">
					<a class="inv" href="/artist/{{ urlencode(str_replace("/","",$artist->name))."/".
					urlencode(str_replace("/","",$album['name']))}}">{{ $artist->name." " .$album['name']}}</a>
					<a class="cover" onclick="lookForAlbum('{{ str_replace("'","\\'",$artist->name)}}',
					'{{ str_replace("'","\\'",$album['name'])}}');">
						<img style="width:64px;height:64px;" @if ($i>4){{ "data-" }}@endif src="{{$album["image"]}}" />
					</a>
				</td>
				<td>
					<a class="cursor" onclick="lookForAlbum('{{ str_replace("'","\\'",$artist->name)}}',
					'{{ str_replace("'","\\'",$album['name'])}}');">
						{{ $album['name']}}
					</a>
					@foreach($artist->getSpotifyalbums() as $spotifyName => $spotifyHref)
						@if ($spotifyName == strtolower($album['name']))
							<br/>
							<a href="{{$spotifyHref}}" target="_blank"><i class="fa fa-spotify"></i></a>
						@endif
					@endforeach
				</td>
			</tr>
			<?php
			$i++;
		}
	}
}else{
	?>
	<tr>
		<td colspan="2">-</td>
	</tr>
	<?php
}
?>
									
<script>
	$( document ).ready(function() {
		$("#block_albums").css('visibility','visible');
		$("#nexttral").css('visibility','hidden');
		@if ($bAlbums)
			$("#nexttral").css('visibility','visible');
		@endif
	});
</script>