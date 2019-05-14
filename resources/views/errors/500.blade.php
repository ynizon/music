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
					<br/>
					<div class="padding-top:15px">
						<div class="title m-b-md">
							Désolé, nous avons rencontré une erreur serveur. Ce n'était pas censé arriver :(
							<br/><br/>
							<a class="btn btn-primary cursor mybtn" id="backbtn">Retour</a>
						</div>

						<div style="display:none">
							<?php echo $exception->getMessage(). "<br/>ligne ".$exception->getLine()."<br/>Fichier ".$exception->getFile();?>
						</div>
					</div>
				</div>
            </div>
        </div>
	</div>
</div>
@endsection