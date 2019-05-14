@extends('layouts.app')

@section('content')
<?php
$user = Auth::user();
?>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-2">
            <div class="panel panel-default">	
				<div class="panel-heading"><h1>Maintenance</h1></div>			
                <div class="panel-body">
					<div class="title m-b-md">
						<form method="post">
							{{ csrf_field() }}
							
							<div class="form-group">
								<label for="maintenance" class="col-md-4 control-label">Code</label>

								<div class="col-md-6">
									<input type="password" name="maintenance" value="" required autofocus />
								</div>
							</div>
							<br style="clear:both" />
							<div class="form-group">
								<label for="cache" class="col-md-4 control-label">Supprimer le cache</label>

								<div class="col-md-6">
									<input id="cache" type="checkbox" value="reset" name="cache" />
								</div>
							</div>
							
							<div class="form-group">									
								<?php								
								if ($bMaintenance){
									$sBtn = "Supprimer la maintenance";
								}else{
									$sBtn = "Activer la maintenance";
								}
								?>
								<div class="col-md-6">
									<input type="submit" value="<?php echo $sBtn;?>" class="btn btn-primary mybackground"/>
								</div>
							</div>
							
						</form>
					</div>
				</div>
            </div>
        </div>
	</div>
</div>
@endsection