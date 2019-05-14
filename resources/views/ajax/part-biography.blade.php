<?php 								
	if (isset($artist->biography)){
		$tab = json_decode($artist->biography);
		if (isset($tab->artist->bio)){
			?>
			<div itemscope itemtype="http://schema.org/MusicGroup">
				<h3 itemprop="name"><?php echo $artist->name;?></h3>
				<span itemprop="description">
					<?php 
					echo str_replace("User-contributed text is available under the Creative Commons By-SA License; additional terms may apply.","",str_replace("\n","<br/>",str_replace("Read more on ","En savoir plus sur ",str_replace("User-contributed text is available under the Creative Commons By-SA License and may also be available under the GNU FDL.","",str_replace("<a ","<a target='_blank' ",$tab->artist->bio->content)))));
					?>
				</span>
				<br/><a target="_blank" href="http://fr.wikipedia.org/wiki/<?php echo str_replace('"',"'", $artist->name);?>">Voir la fiche wikipédia.</a>
			</div>
		<?php
		}else{
			?>
			Biographie non disponible
			<?php
		}
	}else{
		?>
		Biographie non disponible
		<?php
	}
?>