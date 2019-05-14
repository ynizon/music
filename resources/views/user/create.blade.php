@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Création</div>
                <div class="panel-body">
				    
					{!! Form::open(['url' => 'users', 'method' => 'post', 'class' => 'form-horizontal panel']) !!}	
                        {{ csrf_field() }}

						<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Nom</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="" required autofocus />

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
								
						<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">E-Mail (=login de connexion)<br/>Si vous ne savez pas, mettez client@search-foresight.com</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="" required />

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
						
						<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 control-label">Password</label>

                            <div class="col-md-6">
                                <input id="password" type="text" class="form-control" name="password" value="" required />

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
						
						
						<div class="form-group{{ $errors->has('role') ? ' has-error' : '' }}">
                            <label for="role" class="col-md-4 control-label">Rôle du compte</label>

                            <div class="col-md-6">
                                {!! Form::select('role', $users_roles,"user" , ['class' => 'form-control']) !!}
                            </div>
                        </div>

                       <div class="form-group{{ $errors->has('alert_mail') ? ' has-error' : '' }}">
                            <label for="alert_mail" class="col-md-4 control-label">Alertes mail après les mises à jour</label>

                            <div class="col-md-6">
                                {!! Form::select('alert_mail', array("1"=>"Actif","0"=>"Inactif"),0 , ['onchange'=>'refreshAffectation()','id'=>"alert_mail", 'class' => 'form-control']) !!}
                            </div>
                        </div>
						
						<div class="form-group{{ $errors->has('id_manager') ? ' has-error' : '' }}">
                            <label for="id_manager" class="col-md-4 control-label">Responsable</label>

                            <div class="col-md-6">
                                {!! Form::select('id_manager', $responsables,"user" , ['class' => 'form-control']) !!}
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
					
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
