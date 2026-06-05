@extends('layouts.app')

@section('content')
    <div class="container sonos-dashboard-wrapper">
        <div class="row">
            <!-- Music Selector Column -->
            <div class="col-md-7">
                <div class="sonos-card">
                    <div class="sonos-card-title">
                        <i class="fa fa-music"></i> Sélection de Musiques
                    </div>

                    <!-- Search Input -->
                    <div class="search-container">
                        <i class="fa fa-search"></i>
                        <input type="text" id="artist-search" placeholder="Rechercher un artiste (ex: Muse, ACDC...)" autocomplete="off">
                    </div>

                    <!-- Music Selection Area -->
                    <div id="artist-music-area" style="display: none;">
                        <h3 id="current-artist-name" style="font-weight: 700; margin-bottom: 1rem; font-size: 1.25rem; color: #fff;"></h3>

                        <button class="play-all-btn" id="btn-play-artist-all">
                            <i class="fa fa-play-circle"></i> Lire toute la discographie de l'artiste
                        </button>

                        <!-- Playlists -->
                        <div class="music-list-section" id="section-m3u" style="display: none;">
                            <h4>Listes de Lecture (.m3u)</h4>
                            <ul class="music-list" id="list-m3u"></ul>
                        </div>

                        <!-- Albums/Folders -->
                        <div class="music-list-section" id="section-folders" style="display: none;">
                            <h4>Albums & Dossiers</h4>
                            <ul class="music-list" id="list-folders"></ul>
                        </div>
                    </div>

                    <div id="placeholder-search-text" style="color: #64748b; text-align: center; padding: 2rem 0;">
                        <i class="fa fa-info-circle" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                        Entrez le nom d'un artiste ci-dessus pour afficher ses albums et listes de lecture.
                    </div>
                </div>
            </div>

            <!-- Remote Controls Column -->
            <div class="col-md-5">
                <div class="sonos-card">
                    <div class="sonos-card-title">
                        <i class="fa fa-sliders"></i> Destination & Contrôles
                    </div>

                    <!-- Destination Toggle -->
                    <div class="speaker-selector">
                        @php $isFirst = true; @endphp
                        @foreach ($speakers as $name => $ip)
                            <div class="speaker-option {{ $isFirst ? 'active' : '' }}" data-ip="{{ $ip }}" id="opt-{{ strtolower($name) }}">
                                {{ $name }}
                            </div>
                            @php $isFirst = false; @endphp
                        @endforeach
                    </div>

                    <!-- Now Playing Panel -->
                    <div class="now-playing-container">
                        <div class="now-playing-glow"></div>
                        <div class="now-playing-title" id="status-track">Pas de musique en cours</div>
                        <div class="now-playing-artist" id="status-artist">-</div>
                        <div class="now-playing-album" id="status-album">-</div>
                    </div>

                    <!-- Playback Action Buttons -->
                    <div class="controls-group">
                        <button class="control-btn" id="btn-prev" title="Précédent">
                            <i class="fa fa-step-backward"></i>
                        </button>
                        <button class="control-btn" id="btn-stop" title="Arrêter">
                            <i class="fa fa-stop"></i>
                        </button>
                        <button class="control-btn play-btn" id="btn-play-pause" title="Play / Pause">
                            <i class="fa fa-play" id="play-pause-icon"></i>
                        </button>
                        <button class="control-btn" id="btn-next" title="Suivant">
                            <i class="fa fa-step-forward"></i>
                        </button>
                    </div>

                    <!-- Volume Slider -->
                    <div class="volume-container">
                        <i class="fa fa-volume-down" id="btn-mute" title="Muet"></i>
                        <input type="range" min="0" max="100" class="volume-slider" id="volume-range" value="20">
                        <div class="volume-value" id="volume-lbl">20</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification element -->
        <div class="status-notification" id="toast-notif">
            <i id="toast-icon" class="fa"></i>
            <span id="toast-message">Notification</span>
        </div>
    </div>

    <!-- Sonos Interaction Script -->
    <script>
        $(document).ready(function() {
            // CSRF Setup for Axios/JQuery AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // State variables
            let selectedIp = "{{ array_values($speakers)[0] ?? '' }}";
            let currentArtist = "";
            let currentSubPath = "";
            let isMuted = false;
            let activeTransportState = "STOPPED";

            // Poll Sonos status every 5 seconds
            setInterval(fetchSonosStatus, 5000);
            fetchSonosStatus();

            // Speaker target toggle
            $(".speaker-option").click(function() {
                $(".speaker-option").removeClass("active");
                $(this).addClass("active");
                selectedIp = $(this).data("ip");
                showToast("Haut-parleur : " + $(this).text().trim() + " sélectionné", "success");
                fetchSonosStatus();
            });

            // Initialize JQuery UI Autocomplete for Artists
            $("#artist-search").autocomplete({
                source: "/sonos/autocomplete",
                minLength: 1,
                select: function(event, ui) {
                    const artist = ui.item.value;
                    loadArtistMedia(artist);
                }
            });

            // Load media (folders + M3Us) for selected artist
            function loadArtistMedia(artist, subpath = "") {
                currentArtist = artist;
                currentSubPath = subpath;
                $("#placeholder-search-text").hide();
                $("#artist-music-area").show();

                let headerText = artist;
                if (currentSubPath) {
                    headerText += " <span style='font-size: 1rem; color: #94a3b8; font-weight: normal;'><i class='fa fa-angle-right' style='margin: 0 5px;'></i>" + currentSubPath + "</span>";
                }
                $("#current-artist-name").html(headerText);

                // Fetch directories and playlists
                $.ajax({
                    url: "/sonos/albums",
                    type: "GET",
                    data: { artist: artist, path: currentSubPath },
                    success: function(data) {
                        // Populate M3U Playlists
                        const listM3u = $("#list-m3u");
                        listM3u.empty();
                        if (data.m3us && data.m3us.length > 0) {
                            $("#section-m3u").show();
                            data.m3us.forEach(function(m3u) {
                                const label = m3u.name;
                                const item = $("<li class='music-item'></li>")
                                    .data("path", m3u.path)
                                    .data("type", "m3u8")
                                    .append($("<div class='music-item-info'><i class='fa fa-list-ul'></i><span>" + label + "</span></div>"))
                                    .append($("<i class='fa fa-play-circle music-item-action'></i>"));
                                listM3u.append(item);
                            });
                        } else {
                            $("#section-m3u").hide();
                        }

                        // Populate Album folders
                        const listFolders = $("#list-folders");
                        listFolders.empty();

                        // Prepend parent directory option if inside a subfolder
                        if (currentSubPath) {
                            $("#section-folders").show();
                            const parts = currentSubPath.split('/');
                            parts.pop();
                            const parentPath = parts.join('/');
                            const backItem = $("<li class='music-item parent-dir-item'></li>")
                                .data("path", parentPath)
                                .data("type", "back")
                                .append($("<div class='music-item-info'><i class='fa fa-arrow-left'></i><span style='font-weight: bold;'>.. (Retour)</span></div>"));
                            listFolders.append(backItem);
                        }

                        if (data.folders && data.folders.length > 0) {
                            $("#section-folders").show();
                            data.folders.forEach(function(folder) {
                                const label = folder.name;
                                const item = $("<li class='music-item'></li>")
                                    .data("path", folder.path)
                                    .data("type", "folder")
                                    .data("has-m3u", folder.has_matching_m3u)
                                    .data("m3u-path", folder.m3u_path)
                                    .append($("<div class='music-item-info'><i class='fa fa-folder-open'></i><span>" + label + "</span></div>"))
                                    .append($("<i class='fa fa-play-circle music-item-action'></i>"));
                                listFolders.append(item);
                            });
                        } else if (!currentSubPath) {
                            $("#section-folders").hide();
                        }
                    },
                    error: function(xhr) {
                        const errMsg = xhr.responseJSON ? xhr.responseJSON.error : "Impossible de charger les albums de cet artiste.";
                        showToast(errMsg, "error");
                    }
                });
            }

            // Click handler to navigate folders or play music
            $(document).on("click", ".music-item", function(e) {
                const path = $(this).data("path");
                const type = $(this).data("type");
                const hasM3u = $(this).data("has-m3u");
                const m3uPath = $(this).data("m3u-path");

                // If play action clicked
                if ($(e.target).hasClass("music-item-action") || $(e.target).parent().hasClass("music-item-action")) {
                    if (type === "folder" && hasM3u) {
                        playMusic("m3u", m3uPath);
                    } else {
                        playMusic(type, path);
                    }
                    return;
                }

                if (type === "folder" || type === "back") {
                    loadArtistMedia(currentArtist, path);
                } else {
                    playMusic(type, path);
                }
            });

            // Click handler to play everything by the artist
            $("#btn-play-artist-all").click(function() {
                playMusic("folder", ""); // Empty path means play base artist folder
            });

            // Function to start playing music
            function playMusic(type, path) {
                showToast("Lancement de la musique...", "info");

                $.ajax({
                    url: "/sonos/play",
                    type: "POST",
                    data: {
                        ip: selectedIp,
                        type: type,
                        artist: currentArtist,
                        path: path
                    },
                    success: function(data) {
                        showToast(data.message || "Lecture démarrée !", "success");
                        setTimeout(fetchSonosStatus, 1500);
                    },
                    error: function(xhr) {
                        const errMsg = xhr.responseJSON ? xhr.responseJSON.error : "Une erreur est survenue lors du lancement de la lecture.";
                        showToast(errMsg, "error");
                    }
                });
            }

            // Remote Controls Event Handlers
            $("#btn-play-pause").click(function() {
                const action = (activeTransportState === "PLAYING") ? "pause" : "play";
                sendControl(action);
            });

            $("#btn-stop").click(function() {
                sendControl("stop");
            });

            $("#btn-next").click(function() {
                sendControl("next");
            });

            $("#btn-prev").click(function() {
                sendControl("previous");
            });

            $("#btn-mute").click(function() {
                const nextMute = isMuted ? 0 : 1;
                sendControl("mute", nextMute);
            });

            // Volume range control with debounce
            let volTimeout;
            $("#volume-range").on("input", function() {
                const volume = $(this).val();
                $("#volume-lbl").text(volume);

                clearTimeout(volTimeout);
                volTimeout = setTimeout(function() {
                    sendControl("volume", volume);
                }, 250);
            });

            // Helper to trigger commands on Sonos
            function sendControl(action, value = null) {
                $.ajax({
                    url: "/sonos/control",
                    type: "POST",
                    data: {
                        ip: selectedIp,
                        action: action,
                        value: value
                    },
                    success: function(data) {
                        fetchSonosStatus();
                    },
                    error: function(xhr) {
                        const errMsg = xhr.responseJSON ? xhr.responseJSON.error : "Erreur de contrôle Sonos.";
                        showToast(errMsg, "error");
                    }
                });
            }

            // Fetch current playing status
            function fetchSonosStatus() {
                $.ajax({
                    url: "/sonos/control",
                    type: "POST",
                    data: {
                        ip: selectedIp,
                        action: "status"
                    },
                    success: function(data) {
                        if (data.success) {
                            // Title/Artist
                            const info = data.info;
                            const hasTrack = info.Title && info.Title !== "1"; // "1" or empty shows up if nothing is loaded

                            if (hasTrack) {
                                $("#status-track").text(info.Title).attr("title", info.Title);
                                $("#status-artist").text(info.TitleArtist || info.AlbumArtist || "-").attr("title", info.TitleArtist || info.AlbumArtist || "-");
                                $("#status-album").text(info.Album || "-").attr("title", info.Album || "-");
                            } else {
                                $("#status-track").text("Pas de musique en cours");
                                $("#status-artist").text("-");
                                $("#status-album").text("-");
                            }

                            // Transport State (Play / Pause icon)
                            activeTransportState = data.transport;
                            if (activeTransportState === "PLAYING") {
                                $("#play-pause-icon").removeClass("fa-play").addClass("fa-pause");
                                $(".now-playing-glow").css("background", "radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, rgba(0, 0, 0, 0) 70%)");
                            } else {
                                $("#play-pause-icon").removeClass("fa-pause").addClass("fa-play");
                                $(".now-playing-glow").css("background", "radial-gradient(circle, rgba(78, 121, 155, 0.15) 0%, rgba(0, 0, 0, 0) 70%)");
                            }

                            // Volume
                            $("#volume-range").val(data.volume);
                            $("#volume-lbl").text(data.volume);

                            // Mute state
                            isMuted = (data.mute === "1" || data.mute === 1);
                            if (isMuted) {
                                $("#btn-mute").removeClass("fa-volume-down").addClass("fa-volume-off").css("color", "#ef4444");
                            } else {
                                $("#btn-mute").removeClass("fa-volume-off").addClass("fa-volume-down").css("color", "");
                            }
                        }
                    },
                    error: function() {
                        // Fail silently during background polling
                    }
                });
            }

            // Custom Toast Notification display
            function showToast(message, type = "success") {
                const toast = $("#toast-notif");
                const icon = $("#toast-icon");

                toast.removeClass("success error info");
                icon.removeClass("fa-check-circle fa-times-circle fa-info-circle fa-spinner spinner-loader");

                if (type === "success") {
                    toast.addClass("success");
                    icon.addClass("fa-check-circle");
                } else if (type === "error") {
                    toast.addClass("error");
                    icon.addClass("fa-times-circle");
                } else {
                    toast.addClass("info");
                    icon.addClass("fa-spinner spinner-loader");
                }

                $("#toast-message").text(message);
                toast.addClass("show");

                if (type !== "info") {
                    setTimeout(function() {
                        toast.removeClass("show");
                    }, 3500);
                }
            }
        });
    </script>
@endsection
