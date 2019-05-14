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
							<h1>Contact</h1>
							<div>
								Pour me contacter, écrivez à <a href="mailto:ynizon@gmail.com">ynizon@gmail.com</a><br/>
								Pour me remercier, vous pouvez le donner quelques euros via <a href='https://www.paypal.me/ynizon'>https://www.paypal.me/ynizon</a> (ca permettra d'ajouter des fonctionnalités)
							</div>
						</div>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection