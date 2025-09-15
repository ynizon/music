@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
					<div class="row" id="row1">
						<div class="col-md-4 col_1 mypad">
							<h2>
								Téléchargements&nbsp;&nbsp;
							</h2>
							<div id="spots">
                                <form method="post">
                                    <ul style="list-style: none">
                                        <li>
                                            <input type="checkbox" value="" id="chk_0" onclick="toggleAllCheckboxes(this)">
                                            <label for="chk_0"> - Tous - </label>
                                        </li>
                                    @foreach ($spots as $spot)
                                        <li>
                                            <input type="checkbox" name="spotdls[]" value="{{$spot->id}}"
                                            id="chk_{{$loop->index}}" @if ($sport->todo) checked @endif
                                            <label for="chk_{{$loop->index}}">{{$spot->artist}} - {{$spot->album}}</label>
                                        </li>
                                    @endforeach
                                    </ul>
                                    <input type="submit" value="Enregistrer" />
                                </form>
							</div>
						</div>

                        <div class="col-md-4 col_1 mypad">
                            <h2>
                                Télécharger des playlists
                            </h2>
                            <div id="spots">
                                <form method="post" action="spotdl/playlist">
                                    Nom de la playlist:<br/>
                                    <input type="text" name="playlist_name" value="Votre nom - #nom playlist" /><br/>
                                    Url Spotify:<br/>
                                    <input type="text" name="spotify_url" value="https://open.spotify.com/"><br/>
                                    <input type="submit" value="Enregistrer" />
                                </form>
                            </div>
                        </div>

                        <div class="col-md-4 col_1 mypad">
                        </div>
						<div class="clearfix"> </div>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
