@extends('layouts.app')

@section('content')
<?php
$user = Auth::user();

?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">				
                <div class="panel-body">
					<div class="row">
						<div class="col-md-8 col_1 mypad">
							<br/>
							<h1>Téléchargement local</h1>
							<div>
								<?php
								if ($info != ""){
								?>
									<div class="alert alert-<?php echo $class;?>">
										<?php
										echo $info;
										?>
									</div>
								<?php
								}
								?>
								<form method="post">
									Indiquer l'url Youtube des fichiers à télécharger (1 fichier, ou 1 playlist):
									<input class="form-control" name="url" value="" placeHolder="Exemple https://www.youtube.com/playlist?list=OLAK5uy_n-DCdgZiBxr9s9tBuTSMZ5jVy53jLV5Zk"/>
									<br/>
									<input style="background:#4e799b;border:0;" type="submit" value="Télécharger" class="btn btn-primary"/>
									{{ csrf_field() }}
								</form>
								<br/>
								
								<?php
								if (count($files)>2){
								?>
									<h3>Liste des fichiers disponibles</h3>
									<ul>
										<?php
										foreach ($files as $file){
											if ($file != ".." and $file  != "."){
											?>
												<li><a target='_blank' href='/mydownload/mp3/<?php echo $file;?>'><?php echo $file;?></a></li>
											<?php
											}
										}
										?>									
									</ul>
								<?php
								}
								?>
							</div>
						</div>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection