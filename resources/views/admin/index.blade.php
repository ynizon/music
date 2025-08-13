@extends('layouts.app')

@section('content')
<?php
$user = Auth::user();
?>
<style>
.menu_q{display:none;}
</style>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">				
                <div class="panel-body">
					<div class="row">
						<div class="col-md-12" style="text-align:center">
							<br/>
							<h3>Autoriser une IP</h3>
							<br/>
							<p>
								{{$error}}
							</p>
							<form method="post" action="/admin"><br/>
								@csrf
								<input type="text" name="password" required value="" placeholder="Password"
									   style="width:200px"/>&nbsp;&nbsp;&nbsp;
								<input type="text" name="ip" required value="<?php echo $_SERVER['REMOTE_ADDR'];?>"
									   placeholder="Public IP"
									   style="width:200px"/>&nbsp;&nbsp;&nbsp;
								<input type="submit" class="btn btn-primary mybtn" value="Autoriser" />
							</form>
							<br/>
							<ul style="list-style: none">
								@foreach ($ips as $ip)
									<li>{{$ip}}</li>
								@endforeach
							</ul>
						</div>
						
						<div class="clearfix"> </div>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection