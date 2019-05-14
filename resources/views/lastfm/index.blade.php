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
						<div class="col-md-12" style="text-align:center">
							<br/>
							<h3>Informations Last FM</h3>
							<br/>
							<p>
							Indiquez ici votre pseudo Last FM<br/>pour personnaliser votre page d'accueil selon vos goûts.<br/>
							</p>
							<form method="post" action="/lastfm_login"><br/>
								@csrf
								<input type="text" name="lastfm_login" required value="<?php echo $lastfm_login;?>" style="width:200px"/>&nbsp;&nbsp;&nbsp;
								<input type="submit" class="btn btn-primary mybtn" value="Enregistrer" />
							</form>
						</div>
						
						<div class="clearfix"> </div>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection