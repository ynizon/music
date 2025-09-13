@php use App\Helpers\Helpers; @endphp
@extends('layouts.app')

@section('content')
<div>
    <div class="justify-content-center">
        <?php
		$sTxt = "#text";
		$sArtist = "";
		if (isset($_COOKIE["artist"])){
			$sArtist = $_COOKIE["artist"];
		}

		?>
		<style>
		.menu_q{display:none;}
		</style>
		<div class="banner banner<?php echo rand(1,3);?>">
			<div class="span_1_of_1">
				<h2>Découvrez la musique <br/>que vous aimez</h2>
				<div class="search">
				  <ul class="nav1">
					<li id="search">
						<input onkeypress="if (event.keyCode === 13){homeToGo();}" type="text"
							   name="q2" id="q2" class="home_q"
							   value="<?php if (isset($artist_name)){echo $artist_name;}?>" placeholder="Rechercher"/>
					</li>
					<li id="options" style="text-align:right;" onclick="homeToGo()">
						<a href="#" style="min-width:100%;background-image:none;"><i class="fa fa-search cursor" ></i></a>
						<!--<ul class="subnav">
							<li><a href="#">Album</a></li>
							<li><a href="#">Titre</a></li>
						</ul>
						-->
					</li>
				  </ul>
				</div>
			</div>
		</div>
		<div class="container">
			<div class="grid_1">
				<div id="block_artistesHP" >
					<?php
					$sTxt = "#text";
					$sStart = "";

					$i = 0;
					//Mes gouts
					if (isset($preferences)){
						$tab = array();
						foreach ($preferences as $oArtist){
							$tab[] = $oArtist;
						}
						shuffle($tab);
						foreach ($tab as $oArtist){
							if ($i < 12){
								if ($i == 0){
								?>
									<br/>
									<h3>Accéder aux concerts de vos artistes favoris</h3>		
									<div class="clearfix"></div>
								<?php
								}

								$sImage = "/images/home_default.png";
								if ($oArtist["image"][2]["#text"] != ""){
									$sImage = $oArtist["image"][2]["#text"];
									//$sImage = "/images/home_default.png";
									
									if ($oArtist["mbid"] != ""){
										$sImage = Helpers::getPic($oArtist["mbid"],$sImage);
									}
								}
								
								?>
								<div class="col-md-2 col_1  pointer tc"  >
									<a href='/go/{{ str_replace("/","",str_replace("%2f","",strtolower(urlencode
									($oArtist["name"]))))}}'>
										<h5 class='homeh5'>{{ $oArtist["name"]}}</h5>
										<div class="bloc_home">
											<img style="max-height:174px;margin:auto;" src="{{ $sImage}}"
												 class="img-responsive"
												 alt="{{ str_replace('"','\"',$oArtist["name"])}}"/>
										</div>
									</a>
								</div>			
								<?php
								$i++;
							}		
						}
					}

					//Artistes similaires
					$i = 0;
					if (isset($similar)){
						if (isset($similar->similarartists)){
							$tab = array();
							foreach ($similar->similarartists->artist as $oArtist){
								$tab[] = $oArtist;
							}
							shuffle($tab);
							foreach ($tab as $oArtist){
								if ($i < 12 ){
									if ($i == 0){
									?>
										<div class="clearfix"></div>
										<br/>
										<h3>Découvrez de nouveaux talents</h3>		
										<div class="clearfix"></div>
									<?php
									}
									?>
									<div class="col-md-2 col_1  pointer tc" >
										<a href='/go/{{ str_replace("/","",str_replace("%2f","",strtolower
										(urlencode($oArtist->name))))}}'>
											<h5 class='homeh5'>{{ $oArtist->name}}</h5>
											<div class="bloc_home">
												<?php
												$pic = $oArtist->image[2]->$sTxt;
												if (isset($oArtist->mbid)){
													if ($oArtist->mbid != ""){
														$pic = Helpers::getPic($oArtist->mbid,$pic);
													}
												}
												?>
												<img style="max-height:174px;margin:auto;" src="{{ $pic}}"
													 class="img-responsive"
													 alt="{{ str_replace('"','\"',$oArtist->name)}}"/>
											</div>
										</a>
									</div>			
									<?php
									$i++;
								}		
							}
						}
					}
					?>
				</div>
				
				<div class="clearfix"> </div>
			</div>

			<div class="grid_1">
				<br />
				<h3>Suivez les tendances du moment</h3>
				
				<div id="block_tendanceHP" >
					<?php
					if (isset($artistes->artists)){				
						$i = 0;
						foreach ($artistes->artists->artist as $oArtist){
							if ($i < 12){
							?>
							<div class="col-md-2 col_1 pointer tc">
								<a href='/go/{{ str_replace("/","",str_replace("%2f","",strtolower(urlencode
								($oArtist->name))))}}'>
									<h5 class='homeh5'>{{ $oArtist->name}}</h5>
									<div class="bloc_home">
										<?php 
										$pic = $oArtist->image[2]->$sTxt;
										if (isset($oArtist->mbid)){
											if ($oArtist->mbid != ""){
												$pic = Helpers::getPic($oArtist->mbid,$pic);
											}
										}
										?>
										<img style="max-height:174px;margin:auto;" src="{{ $pic}}"
										class="img-responsive"
											 alt="{{ str_replace('"','\"',$oArtist->name)}}" />
									</div>
								</a>
							</div>					
							<?php
							}
							$i++;
						}
					}
					?>
				</div>	
					
				<div class="clearfix"> </div>
			</div>
		</div>
    </div>
</div>
@endsection
