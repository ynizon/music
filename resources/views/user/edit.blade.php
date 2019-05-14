@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Modification</div>
                <div class="panel-body">
				    
					{!! Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'put','files'=>true,'class' => 'form-horizontal panel']) !!}
                        {{ csrf_field() }}

						<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Nom</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{!! $user->name !!}" required autofocus />

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
						
						<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">E-Mail</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{!! $user->email !!}" required />

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
						
						<?php
						if (auth::user()->hasRole("Admin") or auth::user()->hasRole("User") or auth::user()->hasRole("Manager")){
						?>
							<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
								<label for="password" class="col-md-4 control-label">Password</label>

								<div class="col-md-6">
									<input id="password" type="text" class="form-control" name="password" value="" />

									@if ($errors->has('password'))
										<span class="help-block">
											<strong>{{ $errors->first('password') }}</strong>
										</span>
									@endif
								</div>
							</div>
						<?php
						}
						?>
						
						<div class="form-group{{ $errors->has('role') ? ' has-error' : '' }}">
                            <label for="role" class="col-md-4 control-label">Rôle du compte</label>

                            <div class="col-md-6">
								<?php
								$roles = config('app.users_roles');
								if (Auth::user()->hasRole("User")){
									unset($roles["Admin"]);
									unset($roles["Manager"]);
								}
								if (Auth::user()->hasRole("Manager")){
									unset($roles["Admin"]);
								}
								?>
                                {!! Form::select('role', $roles,$role , ['class' => 'form-control']) !!}
                            </div>
                        </div>
						
						<div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
                            <label for="status" class="col-md-4 control-label">Statut</label>

                            <div class="col-md-6">
                                {!! Form::select('status', array("1"=>"Actif","0"=>"Inactif"),$user->status , ['onchange'=>'refreshAffectation()','id'=>"status", 'class' => 'form-control']) !!}
                            </div>
                        </div>
						
						<div class="form-group{{ $errors->has('alert_mail') ? ' has-error' : '' }}">
                            <label for="alert_mail" class="col-md-4 control-label">Alertes mail après les mises à jour</label>

                            <div class="col-md-6">
                                {!! Form::select('alert_mail', array("1"=>"Actif","0"=>"Inactif"),$user->alert_mail , ['onchange'=>'refreshAffectation()','id'=>"alert_mail", 'class' => 'form-control']) !!}
                            </div>
                        </div>

						<div class="form-group{{ $errors->has('id_manager') ? ' has-error' : '' }}">
                            <label for="id_manager" class="col-md-4 control-label">Responsable</label>

                            <div class="col-md-6">
                                {!! Form::select('id_manager', $responsables,$user->id_manager, ['class' => 'form-control']) !!}
                            </div>
                        </div>
						
						<div style="display:none" id="bloc_remove_affectations"  class="form-group{{ $errors->has('remove_affectations') ? ' has-error' : '' }}">
                            <label for="remove_affectations" class="col-md-4 control-label">Réaffecter à</label>

                            <div class="col-md-6">								
								<select name="user_id_reaffect" class="form-control" style="display:inline;width:250px;">
									<option value="-1">-</option>
									<?php
									foreach ($users as $usertmp){
										if ($user->status==1 and $usertmp->id != $user->id){
										?>
											<option value="<?php echo $usertmp->id;?>"><?php echo $usertmp->name;?></option>
										<?php
										}
									}
									?>									
								</select>
                            </div>
                        </div>
                       
					    <div class="form-group{{ $errors->has('attachments') ? ' has-error' : '' }}">
                            <label for="attachments" class="col-md-4 control-label">Logo (jpg,png) (hauteur:50px max)</label>

                            <div class="col-md-6">
                                <input id="attachments" type="file" class="form-control" name="attachments" value="" />

                                @if ($errors->has('attachments'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('attachments') }}</strong>
                                    </span>
                                @endif
								
								<?php
								if (count($files)>0){
								?>
								<ul>
									<?php
									foreach ($files as $file){
										?>
										<li>
											<a href='/files/users/<?php echo $user->id;?>/getFile?file=<?php echo basename($file);?>'><?php echo basename($file);?></a> - <a onclick="if (window.confirm('Confirmez la suppression ?')){window.location.href='/files/users/<?php echo $user->id;?>/removeFile?file=<?php echo basename($file);?>';}"><i class="fa fa-remove"></i></a><br/>
											<img height="50" src="data:image/<?php if (strpos($file,"png")!==false){echo "png";}else{echo "jpg";}?>;base64,<?php echo base64_encode(file_get_contents($file));?>" />
										</li>
										<?php
									}
									?>
								</ul>
								<?php
								}
								?>
                            </div>
                        </div>
						
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Enregistrer
                                </button>

                            </div>
                        </div>                    
					{!! Form::close() !!}
					
					<script>
						//Affiche la case uniquement si status = 0
						function refreshAffectation(){
							if ($("#status").val() == 0){
								$("#bloc_remove_affectations").show();
							}else{
								$("#bloc_remove_affectations").hide();
							}
						}
						refreshAffectation();
					</script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
