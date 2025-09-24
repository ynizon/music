<x-filament-panels::page>
    {{-- Page content --}}

    <div class="fi-ta-ctn fi-ta-ctn-with-header">
        <div class="fi-ta-header-ctn">
            <div class="fi-ta-header">
                <h2 class="fi-ta-header-heading">Ajouter un téléchargement</h2>
            </div>
        </div>
        <div class="fi-ta-header-ctn">
            <form class="fi-ta-header-cell" method="post" action="/save">
                <div class="fi-sc  fi-sc-has-gap fi-grid">

                    <div class="fi-grid-col">
                        <div class="fi-sc-component">
                            <div class="fi-fo-field fi-fo-text-input-wrp">
                                <div class="fi-fo-field-label-col">
                                    <div class="fi-fo-field-label-ctn ">
                                        <label for="mountedActionSchema0.name" class="fi-fo-field-label">
                                            <span class="fi-fo-field-label-content">
                                                Artiste / Utilisateur
                                                <sup class="fi-fo-field-label-required-mark">*</sup>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="fi-fo-field-content-col">
                                    <div class="fi-input-wrp fi-fo-text-input">
                                        <div class="fi-input-wrp-content-ctn">
                                            <select  class="fi-select-input" name="username">
                                                <optgroup label="Artiste">
                                                    @foreach ($artists as $artist)
                                                        <option value="{{$artist}}">{{$artist}}</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="Utilisateur">
                                                    @foreach ($users as $user)
                                                        <option value="@{{$user}}">{{$user}}</option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="fi-grid-col">
                        <div class="fi-sc-component">
                            <div class="fi-fo-field fi-fo-text-input-wrp">
                                <div class="fi-fo-field-label-col">
                                    <div class="fi-fo-field-label-ctn ">
                                        <label for="mountedActionSchema0.name" class="fi-fo-field-label">
                                            <span class="fi-fo-field-label-content">
                                                Nom <sup class="fi-fo-field-label-required-mark">*</sup>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="fi-fo-field-content-col">
                                    <div class="fi-input-wrp fi-fo-text-input">
                                        <div class="fi-input-wrp-content-ctn">
                                            <input class="fi-input" required type="text" name="playlist"
                                                   placeholder="#1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="fi-grid-col">
                        <div class="fi-sc-component">
                            <div class="fi-fo-field fi-fo-text-input-wrp">
                                <div class="fi-fo-field-label-col">
                                    <div class="fi-fo-field-label-ctn ">
                                        <label for="mountedActionSchema0.name" class="fi-fo-field-label">
                                            <span class="fi-fo-field-label-content">
                                                Url Spotify <sup class="fi-fo-field-label-required-mark">*</sup>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="fi-fo-field-content-col">
                                    <div class="fi-input-wrp fi-fo-text-input">
                                        <div class="fi-input-wrp-content-ctn">
                                            <input class="fi-input" required type="text" name="spotify_url"
                                                   placeholder="http://open.spotify.com">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="fi-grid-col">
                        <div class="fi-sc-component">
                            <div class="fi-modal-footer-actions">
                                <input type="submit" value="Ajouter" class="fi-color fi-color-primary fi-bg-color-400
                                hover:fi-bg-color-300 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-900
                                hover:fi-text-color-800 dark:fi-text-color-950 dark:hover:fi-text-color-950 fi-btn
                                fi-size-md fi-ac-btn-action"/>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
