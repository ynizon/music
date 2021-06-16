@extends('layouts.app')

@section('content')
<?php
$user = Auth::user();
$sUrl = config("app.url").str_replace("/ajax/","/",$_SERVER['REQUEST_URI']);
$sTitreShare = config("app.name");
if (isset($artist)){
	$sTitre = $artist->name."|" .config("app.name");
	$sTitreShare = $artist->name;
}

//Toutes les infos dans un tableau
$tab = array();
if (isset($artist)){
	$tab['artist']= $artist;
}
if (isset($album)){
	$tab['album']= $album;
}
if (isset($title)){
	$tab['title']= $title;
}

?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">

					<div id="warning_first" class="alert alert-warning alert-dismissible fade show inv" role="alert" style="margin-top:10px">
					  <strong>Bonjour visiteur ! </strong><br/>Tu trouveras les informations concernant ton artiste et les artistes similaires sur le coté gauche.<br/>
					  Les albums se trouvent dans le bloc central. Enfin, je te laisse le choix des vidéos à lancer dans les 2 dernières colonnes.<br/>
					  N'hésite pas à partager ce site, ni à me contacter pour me suggérer des améliorations via les liens du pied de page.
					  <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="$('#warning_first').fadeOut();createCookie('first_visit','1','365');">
						<span aria-hidden="true">&times;</span>
					  </button>
					</div>

					<div class="row" id="row1">
						<div class="col-md-4 col_1 mypad">
							<div style="inv">
								<input type="hidden" id="btn_url" value="<?php echo $sUrl;?>" />
								<input type="hidden" id="btn_artist" value="<?php echo $sTitreShare;?>" />
							</div>
							<h2>
								Informations <span class="inv" >sur <?php echo $artist->name;?></span>
								&nbsp;&nbsp;
								<a target="_blank" href="#" id="btnfacebook" title="Partager le lien sur Facebook" style="font-size:20px" class="mycolor cursor" ><i class="fa fa-facebook"></i></a>
								&nbsp;&nbsp;
								<a target="_blank" href="#" id="btntwitter" title="Partager le lien sur Twitter" style="font-size:20px" class="mycolor cursor" ><i class="fa fa-twitter"></i></a>
							</h2>
							<div id="biography">
								@include('ajax.part-biography', array('artist'=>$artist))
							</div>
						</div>
						<div class="col-md-4 col_1 mypad">
							<h2>Albums <span class="inv">de <?php echo $artist->name;?></span></h2>
							<div id="album_detail" class="inv">
								<?php
								if (isset($album)){
									?>
									@include('ajax.part-infoalbum', array('artist'=>$artist,'album'=>$album))
									<?php
								}
								?>
							</div>
							<div class="blockvide" id="block_albums">
								<table class="mytable myborder table table-striped" width="100%">
									<tbody id="albums">
										@include('ajax.part-albums', array('artist'=>$artist))
									</tbody>
									<tfoot>
										<tr>
											<td colspan="2" class="tc bordertop">
												<br class="removemobile"/>
												<img title="Utilisez les flèches du clavier pour aller plus vite" class="cursor " style="visibility:hidden;" src="/images/skip-backward-icon.png" onclick="pagination(-5,'tral');"  id="prevtral"/>
												<img title="Utilisez les flèches du clavier pour aller plus vite" class="cursor " style="visibility:hidden;" src="/images/skip-forward-icon.png" onclick="pagination(5,'tral');" id="nexttral"/>
											</td>
										</tr>
									</tfoot>
								</table>
							</div>
							<div style="display:none" id="loader" ><img class="rotate" src="/images/default_rotate.png" /></div>
							<script>
								<?php
								if (isset($album)){
									?>
									showAlbum();
									<?php
								}
								?>
							</script>
						</div>

						<div class="col-md-4 col_1 mypad">
							<h2>Vidéo</h2>
							<div id="block_youtube" style="max-height:390px;overflow:hidden">
								<a name="youtube"></a>
								<div id="pub_amazon" style="text-align:center">
									Cliquez sur une vidéo pour la lancer.<br/>
									<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
									<!-- Music Gameandme -->
									<?php
									/*
									<ins class="adsbygoogle"
										 style="display:block"
										 data-ad-client="ca-pub-1192949141732311"
										 data-ad-slot="3769669919"
										 data-ad-format="auto"></ins>
									<script>
									(adsbygoogle = window.adsbygoogle || []).push({});
									</script>
									*/
									?>
									<script data-ad-client="ca-pub-1192949141732311" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
								</div>
								<iframe allow="autoplay" id="youtube" width="480" height="360" src="" frameborder="0" allowfullscreen="allowfullscreen" frameBorder="0" scrolling="no" style="display:none"></iframe>
								<div style="text-align:center">
									<?php
									if (config("app.DOWNLOAD_AVAILABLE")){
									?>
										<a target="_blank" class="cursor" style="display:none" href='#' id="download" >Télécharger le MP3</a>
									<?php
									}

                                    if (config("app.SONOS_AVAILABLE")){
                                        ?>
                                        <div id="sonos_bloc" style="display:none" >
                                            <?php
                                            if (config("app.DOWNLOAD_AVAILABLE")){
                                                echo " - ";
                                            }
                                            ?>
                                            <a target="_blank" class="cursor" href='#' id="sonos" >Envoyer au SONOS</a>
                                        </div>
                                        <?php
                                    }
									?>
									<span id="link_amazon"></span>
								</div>
							</div>
						</div>


						<div class="col-md-4 col_1 mypad">
							<div class="blockvide" id="block_artistes">
								<table class="mytable myborder table table-striped" width="100%">
									<thead>
										<tr>
											<th colspan="2">
												<h2>Artistes similaires <span class="inv">à <?php echo $artist->name;?></span></h2>
												<div id="artistes_query" class="myquery"></div>
											</th>
										</tr>
									</thead>
									<tbody id="artistes">
										@include('ajax.part-similars', array('artist'=>$artist))
									</tbody>
									<tfoot>
										<tr>
											<td colspan="2" class="tc bordertop">
												<br class="removemobile" />
												<img title="Utilisez les flèches du clavier pour aller plus vite" class="cursor " style="visibility:hidden;" src="/images/skip-backward-icon.png" onclick="pagination(-5,'tr');" id="prevtr">
												<img title="Utilisez les flèches du clavier pour aller plus vite" class="cursor " style="visibility: visible;" src="/images/skip-forward-icon.png" onclick="pagination(5,'tr');" id="nexttr">
											</td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>

						<div class="col-md-4 col_1 mypad">
							<div class="blockvide" id="block_albums_youtube">
								<table class="mytable myborder table table-striped" width="100%">
									<thead>
										<tr>
											<th colspan="2">
												<h2>Vidéos des albums <span class="inv">de <?php echo $artist->name;?></span></h2>
												<input title="Vous pouvez faire votre propre recherche ici" type="text" id="albums_youtube_query" value="<?php echo $artist->name;?> album" class="myquery" onkeypress="if(event.keyCode == 13){searchFor('albums_youtube',this);}">
											</th>
										</tr>
									</thead>
									<tbody id="albums_youtube">
										@include('ajax.part-videos', $tab)
									</tbody>
									<tfoot>
										<tr>
											<td colspan="2" class="tc bordertop">
												<br class="removemobile"/>
												<img title="Utilisez les flèches du clavier pour aller plus vite" class="cursor " style="visibility:hidden;" src="/images/skip-backward-icon.png" onclick="pagination(-5,'trya');" id="prevtrya"/>
												<img title="Utilisez les flèches du clavier pour aller plus vite" class="cursor " style="visibility:hidden;" src="/images/skip-forward-icon.png" onclick="pagination(5,'trya');"  id="nexttrya"/>
											</td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>

						<div class="col-md-4 col_1 mypad">
							<div class="blockvide" id="block_lives_youtube">
								<table class="mytable myborder table table-striped" width="100%">
									<thead>
										<tr>
											<th colspan="2">
												<h2>Vidéos des concerts <span class="inv">de <?php echo $artist->name;?></span></h2>
												<input title="Vous pouvez faire votre propre recherche ici" type="text" id="lives_youtube_query" value="<?php echo $artist->name;?> live" class="myquery" onkeypress="if(event.keyCode == 13){searchFor('lives_youtube',this);}">
											</th>
										</tr>
									</thead>
									<tbody id="lives_youtube">
										@include('ajax.part-lives', $tab)
									</tbody>
									<tfoot>
										<tr>
											<td colspan="2" class="tc bordertop">
												<br class="removemobile"/>
												<img title="Utilisez les flèches du clavier pour aller plus vite" class="cursor " style="visibility:hidden;" src="/images/skip-backward-icon.png" onclick="pagination(-5,'tryl');" id="prevtryl"/>
												<img title="Utilisez les flèches du clavier pour aller plus vite" class="cursor " style="visibility:hidden;" src="/images/skip-forward-icon.png" onclick="pagination(5,'tryl');" id="nexttryl"/>
											</td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>

						<div class="clearfix"> </div>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
