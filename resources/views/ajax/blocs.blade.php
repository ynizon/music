<?php
use App\Providers\HelperServiceProvider;
?>
$("#hr").css('visibility','visible');
<?php
if (isset($biography)){
	?>
	$("#biography").html(<?php echo json_encode($biography);?>);
	<?php
}
if (isset($albums)){
	?>
	$("#albums").html(<?php echo json_encode($albums);?>);	
	<?php
}
if (isset($similars)){
	?>
	$("#artistes").html(<?php echo json_encode($similars);?>);
	<?php
}

if (isset($videos)){
	?>
	$("#albums_youtube").html(<?php echo json_encode($videos);?>);
	<?php
}

if (isset($lives)){
	?>
	$("#lives_youtube").html(<?php echo json_encode($lives);?>);
	<?php
}

if (isset($artist_name)){
	?>
	$("#menu_name").html(<?php echo json_encode(HelperServiceProvider::replaceUpperChar($artist_name));?>);
	$("#title_seo").html(<?php echo json_encode($artist_name);?>);
	$("#albums_youtube_query").attr("value",<?php echo json_encode($artist_name." album");?>);
	$("#lives_youtube_query").attr("value",<?php echo json_encode($artist_name." live");?>);
	$("#btn_url").val(<?php echo json_encode(config("app.url")."/artist/".HelperServiceProvider::replaceUpperChar($artist_name));?>);
	$("#btn_artist").val(<?php echo json_encode(HelperServiceProvider::replaceUpperChar($artist_name));?>);
	addShareBtn();
	<?php
}

if (isset($info_album)){
	?>
	$("#album_detail").html(<?php echo json_encode($info_album);?>);
	showAlbum();
	<?php
}
?>

loadImages();
$("#loader").hide();

