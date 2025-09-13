<table class="mytable myborder table table-striped" width="100%">
	<tbody id="albums">
	@php
	/** @var App\Models\Artist $artist */

	//Liste albums
	$bAlbums = false;

    if (count($artist->getTopalbums()) > 4){
		$bAlbums = true;
	}
    @endphp
	@if (count($artist->getTopalbums()) > 0)
		@foreach ($artist->getTopalbums() as $album)
			@if ($album['name'] != "(null)")
				<tr id="tral_{{ $loop->index}}" style="@if($loop->index>4) display:none @endif"
					class="diapotral">
					<td width="61" class="tc tcimage">
						<a class="inv" href="/artist/{{ urlencode(str_replace("/","",$artist->getName()))."/".urlencode
						(str_replace("/","",$album['name']))}}">{{ $artist->getName()." " .$album['name']}}</a>
						<a class="cover" onclick="lookForAlbum('{{ str_replace("'","\\'",$artist->getName())}}','{{ str_replace("'","\\'",$album['name'])}}');">
							<img style="width:64px;height:64px;" @if ($loop->index > 4)
								{{ "data-" }}
							@endif src="{{$album["image"]}}" alt="image"/>
						</a>
					</td>
					<td>
						<a class="cursor" onclick="lookForAlbum('{{ str_replace("'","\\'",$artist->getName())}}','{{ str_replace("'","\\'",$album['name'])}}');">
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
			@endif
		@endforeach
	@else
		<tr>
			<td colspan="2">-</td>
		</tr>
	@endif
	</tbody>
	<tfoot>
	<tr>
		<td colspan="2" class="tc bordertop">
			<br class="removemobile"/>
			<img title="Utilisez les flèches du clavier pour aller plus vite"
				 class="cursor " style="visibility:hidden;"
				 src="/images/skip-backward-icon.png" onclick="pagination(-5,'tral');"
				 id="prevtral" alt="Précédent"/>
			<img title="Utilisez les flèches du clavier pour aller plus vite"
				 class="cursor " style="visibility:@if ($bAlbums) visible @else hidden @endif"
				 src="/images/skip-forward-icon.png" onclick="pagination(5,'tral');"
				 id="nexttral" alt="Suivant"/>
		</td>
	</tr>
	</tfoot>
</table>
