<?php
use App\Providers\HelperServiceProvider;
?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="/images/favicon.png" />

	<?php
	//Recup des METAS
	$sUrl = config("app.url").$_SERVER['REQUEST_URI'];
	$iPos = strpos($sUrl,"?");
	if ($iPos !== false){
		$sUrl = substr($sUrl,0,$iPos);
	}

	$sImage = config("app.url")."/images/hp/1.png";
	$sDescription = "Découvrez la musique que vous aimez";
	$sTitre = config("app.name");
	$sTitreShare = config("app.name");
	if (isset($artist)){
		if (isset($artist->biography)){
			$biography = json_decode($artist->biography,true );
			if (isset($biography["artist"])){
				$sDescription = HelperServiceProvider::extrait(strip_tags($biography["artist"]["bio"]["summary"]));
			}
		}


		$sTitre = $artist->name." - toutes les vidéos et les concerts sur " .config("app.name");
		$sTitreShare = $artist->name;

		if (isset($artist->biography)){
			$json = json_decode($artist->biography,true);
			if (isset($json["artist"]["image"])){
				foreach ($json["artist"]["image"] as $image){
					if ($image["size"] == ""){
						$sImage = $image["#text"];
					}
				}
			}
		}
	}
	if (isset($album)){
		$sTitre = $artist->name." - ".$album->name;
		if (isset($title)){
			$sTitre = $artist->name." - ".$album->name. " - ".$title->name;
		}
	}

	?>
	<link rel="canonical" href="<?php echo $sUrl;?>" />
	<meta property="og:type" content="website">
	<meta property="og:url" content="<?php echo $sUrl;?>">
	<meta property="og:title" content="<?php echo $sTitre;?>">
	<meta property="og:image" content="<?php echo $sImage;?>">
	<meta property="og:description" content="<?php echo $sDescription;?>">
	<meta name="description" content="<?php echo $sDescription;?>" />
	<meta property="og:site_name" content="<?php echo config("app.name");?>">
	<meta property="og:locale" content="fr_FR">
	<!-- Next tags are optional but recommended -->
	<meta property="og:image:width" content="398">
	<meta property="og:image:height" content="585">

	<meta name="twitter:card" content="summary">
	<meta name="twitter:site" content="@enpix">
	<meta name="twitter:creator" content="@enpix">
	<meta name="twitter:url" content="<?php echo $sUrl;?>">
	<meta name="twitter:title" content="<?php echo $sTitre;?>">
	<meta name="twitter:description" content="<?php echo $sDescription;?>">
	<meta name="twitter:image" content="<?php echo $sImage;?>">

	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="/images/favicon.png">
	<meta name="theme-color" content="#ffffff">


    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title id="title_seo"><?php echo $sTitre;?></title>

    <!-- Scripts -->
	<?php
	if (config("app.debug")){
		?>
		<script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
		<script src="{{ asset('js/jquery-ui.min.js') }}"></script>

		<!-- <script src="{{ asset('js/jquery.ui.datepicker-fr.js') }}"></script> -->
		<script src="{{ asset('js/utils.js') }}"></script>
	<?php
	}else{
		?>
		<script src="{{ asset('js/app.js') }}" ></script>
		<?php
	}
	?>


	<?php
		if (config("app.ANALYTICS_UA") != ""){
		?>
			<script>
			  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			  ga('create', '<?php echo config("app.ANALYTICS_UA");?>', 'auto');
			  ga('send', 'pageview');

			</script>
		<?php
		}
	?>


	<!-- Styles -->
	<?php
	if (config("app.debug")){
		?>
		<!-- Fonts -->
		<link href="{{ asset('css/bootstrap/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" >
		<link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('css/jquery-ui-1.8.17.custom.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('css/styles.css') }}" rel="stylesheet" type="text/css">
	<?php
	}else{
		?>
		<link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css">
	<?php
	}
    ?>

    <script>
        $( document ).ready(function() {
            var params = getQueryParams(window.location.search);
            if (params["play"]){
                loadVideo(params["play"], params["title"]);
            }
        });
    </script>
</head>
<body>
    <div id="app">

        <nav class="navbar navbar-expand-md navbar-light navbar-laravel navbar-custom">
            <div class="container">
   				<span style="float:left"><a href="/" title="Accueil"><img width="40" src="/images/logo.png" alt="<?php echo config("app.name");?>"/></a></span>

				<a  class="navbar-brand" href="{{ url('/') }}">
                    <h1 style="margin-top:5px;padding-left:15px;" id="menu_name"><?php if (isset($artist)){echo HelperServiceProvider::replaceUpperChar($artist->name);}else{echo config("app.name");}?></h1>
                </a>
                <button id="toggleNav" class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
						<li style="padding:5px" class="menu_q">
							<input type="text" placeholder="Artiste" name="q" id="q" value="<?php if (isset($artist)){echo $artist->name;}?>" onkeypress="$('#album').val('');$('#title').val('');if (event.keyCode == 13){searchX();}"/>
						</li>
						<li style="padding:5px" class="menu_q">
							<input type="text" placeholder="Album" name="album" id="album" value="<?php if (isset($album)){echo $album->name;}?>" onkeypress="$('#title').val('');if (event.keyCode == 13){searchX();}"/>
						</li>
						<li style="padding:5px" class="menu_q">
							<input type="text" placeholder="Titre" name="title" id="title" value="<?php if (isset($title)){echo $title->name;}?>" onkeypress="if (event.keyCode == 13){searchX();}"/>
						</li>
						<li style="padding:5px" class="menu_q">
							&nbsp;
							<i title="Rechercher" class="fa fa-search cursor" style="color:#fff;font-size:20px;padding:7px;"  onclick="searchX()"></i>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						</li>

						 <li class="nav-item">
							<a class="nav-link" href="/lastfm_login">Mon compte</a>
						</li>

						<?php
						/*
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
						*/
						?>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
			<div class="container">
				@if(session()->has('ok'))
					<div class="alert alert-success alert-dismissible">{!! session('ok') !!}</div>
				@endif

				@if(session()->has('error'))
					<div class="alert alert-danger  alert-dismissible"><?php echo  session('error');?></div>
				@endif
			</div>
            @yield('content')
        </main>

		<div class="grid_3">
		  <div class="container">
			 <ul id="footer-links">
				<li><a href="/faq">Faq</a></li>
				<li style="color:#fff"> | </li>
				<li><a href="/contact">Contact</a></li>
				<li style="color:#fff"> | </li>
				<li><a color="#fff" target="_blank" href="https://www.paypal.me/ynizon">Faire un don</a></li>
				<li style="color:#fff"> | </li>
				<li><a color="#fff" target="_blank" href="https://www.gameandme.fr/creation-web/refonte-du-site-de-musique-music-gameandme-fr/#comments">Suggestions</a></li>
			 </ul>
			 <p>
				<a href="https://www.gameandme.fr/" target="_blank">LEAD DEVELOPPEUR PHP ET EXPERT WEB (SEO) A NANTES - Mon Blog</a>
			 </p>
		  </div>
		</div>
    </div>
</body>
</html>
