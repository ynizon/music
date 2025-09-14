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
                                    <ul style="list-style: none">
                                        <li>
                                            <input type="checkbox" value="" id="chk_0">
                                            <label for="chk_0"> - Tous - </label>
                                        </li>
                                    @foreach ($spots as $spot)
                                        <li>
                                            <input type="checkbox" value="{{$spot->id}}" id="chk_{{$loop->index}}">
                                            <label for="chk_{{$loop->index}}">{{$spot->artist}} - {{$spot->album}}</label>
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
