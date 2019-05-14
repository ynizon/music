<?php
$sTxt = "#text";
$bSimilar = false;
if (isset($artist->similar )){
	$tab = json_decode($artist->similar);
	$i=0;
	if (isset($tab->similarartists->artist)){
		if (count($tab->similarartists->artist)==0){
			?>
			<tr>
				<td>&nbsp;</td>
				<td>Information non disponible</td>
			</tr>
			<?php
		}else{
			foreach ($tab->similarartists->artist as $oArtist){
				?>
				<tr id="tr_<?php echo $i;?>" style="<?php if($i>4){echo 'display:none';$bSimilar = true;}?>"  class="diapotr">
					<td class="tc tcimage" width="120">
						<a class="inv" href="/artist/<?php echo urlencode(str_replace("/","",$oArtist->name));?>"><?php echo $oArtist->name;?></a>
						<a class="cover" onclick="lookForArtist('<?php echo str_replace("'","\\'",$oArtist->name);?>');">
							<?php
							$sImage = "/images/default_rotate.png";
							if  (trim($oArtist->image[2]->$sTxt)!=""){
								$sImage = $oArtist->image[2]->$sTxt;
							}
							
							if ($oArtist->mbid != ""){
								$sImage = Helpers::getPic($oArtist->mbid,$sImage);
							}
							?>
							<img height="90" width="120" <?php if ($i>4){echo "data-";} ?>src="<?php echo $sImage;?>" />
						</a>
					</td>
					<td>
						<a style="display:none" onclick="lookForArtist('<?php echo str_replace("'","\\'",$oArtist->name);?>');"><?php echo $oArtist->name;?></a>
						<a class="cursor" onclick="lookForArtist('<?php echo str_replace("'","\\'",$oArtist->name);?>');"><?php echo $oArtist->name;?></a>
					</td>
				</tr>
				<?php
				$i++;
			}
		}
	}else{
		?>
		<tr>
			<td>&nbsp;</td>
			<td>Information non disponible</td>
		</tr>
		<?php
	}
}else{
	?>
	<tr>
		<td>&nbsp;</td>
		<td>Information non disponible</td>
	</tr>
	<?php
}
?>									

<script>
	$( document ).ready(function() {
		$("#block_artistes").css('visibility','visible');
		$("#nexttr").css('visibility','hidden');
		<?php
		if ($bSimilar){
			?>
			$("#nexttr").css('visibility','visible');
			<?php
		}
		?>
	});
</script>