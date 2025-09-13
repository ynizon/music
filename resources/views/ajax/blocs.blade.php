@php
use App\Helpers\Helpers;
@endphp
$("#hr").css('visibility','visible');
@if (isset($biography))
	$("#biography").html({!! json_encode($biography)!!});
@endif
@if (isset($albums))
	$("#block_albums").html({!!json_encode($albums)!!});
@endif
@if (isset($similars))
	$("#block_artistes").html({!!json_encode($similars)!!});
@endif
@if (isset($videos))
	$("#block_albums_youtube").html({!!json_encode($videos)!!});
@endif
@if (isset($lives))
	$("#block_lives_youtube").html({!!json_encode($lives)!!});
@endif
@if (isset($artist_name))
	$("#menu_name").html({!! json_encode(Helpers::replaceUpperChar($artist_name))!!});
	$("#title_seo").html({!!json_encode($artist_name)!!});
	$("#albums_youtube_query").attr("value",{!! json_encode($artist_name." album")!!});
	$("#lives_youtube_query").attr("value",{!! json_encode($artist_name." live")!!});
	$("#btn_url").val({!! json_encode(config("app.url")."/artist/".Helpers::replaceUpperChar
($artist_name))!!});
	$("#btn_artist").val({!!json_encode(Helpers::replaceUpperChar($artist_name))!!});
	addShareBtn();
@endif
@if (isset($info_album))
	$("#album_detail").html({!!json_encode($info_album)!!});
	showAlbum();
@endif

loadImages();
$("#loader").hide();