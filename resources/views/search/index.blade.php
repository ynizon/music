@php
	use Illuminate\Support\Facades\Auth;
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
@endphp

@extends('layouts.app')

@section('content')
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
							<div class="inv">
								<input type="hidden" id="btn_url" value="{{ $sUrl}}" />
								<input type="hidden" id="btn_artist" value="{{ $sTitreShare}}" />
							</div>
							<h2>
								Informations <span class="inv" >sur {{$artist->getName()}}</span>
								&nbsp;&nbsp;
								<a target="_blank" href="#" id="btnfacebook" title="Partager le lien sur Facebook" style="font-size:20px" class="mycolor cursor" ><i class="fa fa-facebook"></i></a>
								&nbsp;&nbsp;
								<a target="_blank" href="#" id="btntwitter" title="Partager le lien sur Twitter" style="font-size:20px" class="mycolor cursor" ><i class="fa fa-twitter"></i></a>
							</h2>
							<div id="biography">
								@livewire('biography', ['artist'=>$artist])
							</div>
						</div>
						<div class="col-md-4 col_1 mypad">
							<h2>Albums <span class="inv">de {{$artist->getName()}}</span></h2>
							<div id="album_detail" class="inv">
								@if (isset($album))
									@livewire('infoalbum', ['artist'=>$artist, 'album'=>$album])
								@endif
							</div>
							<div class="blockvide" id="block_albums">
								@livewire('albums', ['artist'=>$artist])
							</div>
							<div style="display:none" id="loader" ><img class="rotate" src="/images/default_rotate
							.png" alt="" /></div>
							<script>
								@if (isset($album))
									showAlbum();
								@endif
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
									@if (env("DOWNLOAD_AVAILABLE"))
										<a target="_blank" class="cursor" style="display:none" href='#' id="download" >Télécharger le MP3</a>
									@endif
									<span id="link_amazon"></span>
								</div>
							</div>
						</div>


						<div class="col-md-4 col_1 mypad">
							<div class="blockvide" id="block_artistes" >
								@livewire('similars', ['artist'=>$artist])
							</div>
						</div>

						<div class="col-md-4 col_1 mypad">
							<div class="blockvide" id="block_albums_youtube">
								@livewire('videos', ['artist'=>$artist])
							</div>
						</div>

						<div class="col-md-4 col_1 mypad">
							<div class="blockvide" id="block_lives_youtube">
								@livewire('lives', ['artist'=>$artist])
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
