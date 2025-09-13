@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
					<div class="row" id="row1">
						<div class="col-md-12 col_1 mypad">
							<h2>
								Téléchargements&nbsp;&nbsp;
							</h2>
							<div id="spots">
                                <form method="">
                                    <ul>
                                    @foreach ($spots as $spot)
                                        <li>
                                            <input type="checkbox" value="{{$spot['id']}}">
                                            {{$spot['artist']}} - {{$spot['album']}}
                                        </li>
                                    @endforeach
                                    </ul>
                                </form>
							</div>
						</div>

						<div class="clearfix"> </div>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
