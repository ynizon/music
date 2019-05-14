<?php
//Albums Youtube
$bYoutubeLive = false;

if (isset($artist->youtube_live)){
	$tab = json_decode($artist->youtube_live);
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
			<tr id="tryl_<?php echo $i;?>" style="<?php if($i>4){echo "display:none";$bYoutubeLive = true;}?>" class="diapotryl">
				<td class="tc tcimage" width="120">
					<span class="cover" onclick="loadVideo('<?php echo ($sVideoId);?>','<?php echo urlencode($oVideo->snippet->title);?>');">
						<img <?php if ($i>4){echo "data-";} ?>src="<?php echo $sImage;?>" />
					</span>
				</td>
				<td>
					<span class="pointer" onclick="loadVideo('<?php echo ($sVideoId);?>','<?php echo urlencode($oVideo->snippet->title);?>');">
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
		$("#block_lives_youtube").css('visibility','visible');
		$("#nexttryl").css('visibility','hidden');
		<?php
		if ($bYoutubeLive){
			?>
			$("#nexttryl").css('visibility','visible');
			<?php
		}
		?>
	});
</script>