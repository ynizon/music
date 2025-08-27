<?php
//Liste albums
$sTxt = "#text";
$i = 0;	
$tab = json_decode($artist->topalbums);
$tabAlbums = NULL;
if (isset($tab->topalbums->album)){
	$tabAlbums = $tab->topalbums->album;

	if (!is_array($tab->topalbums->album)){
		$tabAlbums = array($tab->topalbums->album);
	}
}
$bAlbums = false;

if ($tabAlbums[0] != NULL){
	foreach ($tabAlbums as $oAlbum){
		if ($oAlbum->name != "(null)"){
		?>
			<tr id="tral_<?php echo $i;?>" style="<?php if($i>4){echo 'display:none;';$bAlbums = true;}?>" class="diapotral">
				<td width="61" class="tc tcimage">
					<a class="inv" href="/artist/<?php echo urlencode(str_replace("/","",$oAlbum->artist->name))."/". urlencode(str_replace("/","",$oAlbum->name));?>"><?php echo $oAlbum->artist->name." " .$oAlbum->name;?></a>
					<a class="cover" onclick="lookForAlbum('<?php echo str_replace("'","\\'",$oAlbum->artist->name);?>','<?php echo str_replace("'","\\'",$oAlbum->name);?>');">
						<?php
						$sImage = "/images/default_rotate.png";
						if  (trim($oAlbum->image[1]->$sTxt)!=""){
							$sImage = $oAlbum->image[1]->$sTxt;
						}
						?>
						<img style="width:64px;height:64px;" <?php if ($i>4){echo "data-";} ?>src="<?php echo $sImage;?>" />
					</a>
				</td>
				<td>
					<a class="cursor" onclick="lookForAlbum('<?php echo str_replace("'","\\'",$oAlbum->artist->name);?>','<?php echo str_replace("'","\\'",$oAlbum->name);?>');">
						<?php echo $oAlbum->name;?>
					</a>
					@foreach($artist->spotify_albums as $spotifyName => $spotifyHref)
						@if ($spotifyName == strtolower($oAlbum->name))
							<br/>
							<a href="{{$spotifyHref}}" target="_blank"><i class="fa fa-spotify"></i></a>
						@endif
					@endforeach
					<?php
					/*
					if (isset($tabAlbumsAmazon[strtolower($oAlbum->name)])){
						?>
						<a target='_blank' href='<?php echo $tabAlbumsAmazon[strtolower($oAlbum->name)];?>'><i style='padding-left:10px' class='fa fa-shopping-cart'></i></a>
						<?php
					}
					*/
					?>					
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
		<?php
		if ($bAlbums){
			?>
			$("#nexttral").css('visibility','visible');
			<?php
		}
		?>
	});
</script>