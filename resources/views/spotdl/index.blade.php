<html>
    <h2>Todo</h2>
    <ul>
        @foreach ($newspots as $spot)
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
