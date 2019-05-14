<?php
//Detail chansons de l album
if (isset($album->info)){
	
	$tabSongs = array();
	if (isset($album->musescore)){
		$tabSongs = unserialize($album->musescore);	
	}
	$tab = json_decode($album->info);
	?>
	<div itemscope itemtype="http://schema.org/MusicAlbum">
		<h2 style="text-align:left" itemprop="name"><a title="Retour" class="cursor" onclick="backAlbum();" ><i class="fa fa-step-backward mycolor"></i></a>&nbsp;<?php echo $album->name;?></h2>
		<span itemprop="byArtist" class="inv"><?php echo $album->artist;?></span>
		<ul itemprop="track" itemscope itemtype="http://schema.org/ItemList">			
		<?php
		
		if (isset($tab->album)){
			?>
			<span itemprop="numberOfItems" class="inv" content="<?php echo count($tab->album->tracks->track);?>" ></span>
			<?php
			$iPosition = 0;
			foreach ($tab->album->tracks->track as $track){
				$iPosition++;
				$sPartition = "";
				if (isset($tabSongs[$track->name])){
					if ("-" != $tabSongs[$track->name]){
						$sPartition = "<a title='Voir la partition' target='_blank' href='".$tabSongs[$track->name]."'><i style='padding-left:10px' class='fa fa-music'></i></a>";
					}
				}
				?>
				<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<span class="inv" itemprop="position"><?php echo $iPosition;?></span>
					<div itemscope itemtype="http://schema.org/MusicRecording">
						<a class="cursor" onclick="$('#title').val('');$('#q').val('<?php echo str_replace("'","\\'",$album->artist);?>');$('#album').val('<?php echo str_replace("'","\\'",$album->name);?>');$('#title').val('<?php echo str_replace("'","\\'",$track->name);?>');searchX();">
							<span itemprop="name"><?php echo $track->name .'</span> <meta itemprop="duration" content="PT'.gmdate("i",$track->duration)."M".gmdate("s",$track->duration).'S">('.gmdate("H:i:s",$track->duration).')';?></meta>
						</a>
						<?php echo $sPartition;?>
					</div>
				</li>
				<?php
			}
			?>
			</ul>			
		<?php
		}
	?>
	</div>
	<?php
}
?>