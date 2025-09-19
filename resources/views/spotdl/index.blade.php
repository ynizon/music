<html>
    <h2>To check</h2>
    <ul>
        @foreach ($spotsCheck as $spot)
            <li>
                <a target="_blank" href="{{$spot->spotifyurl}}">{{$spot->artist}} - {{$spot->album}}</a>
            </li>
        @endforeach
    </ul>

    <h2>Todo</h2>
    <ul>
        @foreach ($spotsTodo as $spot)
            <li>
                <a target="_blank" href="{{$spot->spotifyurl}}">{{$spot->artist}} - {{$spot->album}}</a>
            </li>
        @endforeach
    </ul>

    <h2>Done</h2>
    <ul>
        @foreach ($spotsDone as $spot)
            <li>
                <a target="_blank" href="{{$spot->spotifyurl}}">{{$spot->artist}} - {{$spot->album}}</a>
            </li>
        @endforeach
    </ul>
</html>
