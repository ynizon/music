<?php
//Albums Youtube
if (isset($artist->youtube_full_album)){
	$tab = json_decode($artist->youtube_full_album);
}

if (isset($album)){
	$tab = json_decode($album->youtube);
}

if (isset($title)){
	$tab = json_decode($title->youtube);
}

$bYoutubealbum = false;
if (isset($tab)){
	$i=0;	
	foreach ($tab->items as $oVideo){												
		$bOk = false;
		switch ($oVideo->id->kind){
			case "youtube#playlist":
				$bOk = false;
				if (isset($oVideo->snippet->thumbnails)){
					$sVideoId = str_replace("/default.jpg","",str_replace("https://i.ytimg.com/vi/","",$oVideo->snippet->thumbnails->default->url))."?list=".$oVideo->id->playlistId;	
					$bOk = true;
				}				
				
				break;
			case "youtube#video":			
				$sVideoId = $oVideo->id->videoId;
				$bOk = true;
				break;
		}
		if ($bOk){
			$sImage = "/images/default_rotate.png";
			if (isset($oVideo->snippet->thumbnails)){
				$sImage = $oVideo->snippet->thumbnails->default->url;
			}
			?>
			<tr id="trya_<?php echo $i;?>" style="<?php if($i>4){echo 'display:none';$bYoutubealbum = true;}?>" class="diapotrya">
				<td class="tc tcimage" width="120">
					<span class="cover" onclick="loadVideo('<?php echo $sVideoId ;?>','<?php echo urlencode($oVideo->snippet->title);?>');">
						<img <?php if ($i>4){echo "data-";} ?>src="<?php echo $sImage;?>" />
					</span>
				</td>
				<td>
					<span class="pointer" onclick="loadVideo('<?php echo ($sVideoId );?>','<?php echo urlencode($oVideo->snippet->title);?>');">
						<?php echo $oVideo->snippet->title;?>
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
	$("#block_albums_youtube").css('visibility','visible');
	$("#nexttrya").css('visibility','hidden');
	<?php
	if ($bYoutubealbum){
		?>
		$("#nexttrya").css('visibility','visible');
		<?php
	}
	?>
});
</script>