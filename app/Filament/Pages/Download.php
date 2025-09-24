<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SpotdlTable;
use Filament\Pages\Page;

class Download extends Page
{
    protected string $view = 'filament.pages.download';
    protected static ?string $title = 'Mes téléchargements';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?int $navigationSort = 2;

    public array $users = [];
    public array $artists = [];

    public function mount(): void
    {
        $users = [];
        if (env("PATH_MUSIC_USER") != ""){
            $path = env("PATH_MUSIC")."/".env("PATH_MUSIC_USER");
            if (is_dir($path)) {
                $dirs = scandir($path);
                foreach ($dirs as $dir) {
                    if ($dir !== '.' && $dir !== '..' && is_dir($path."/".$dir)) {
                        $users[] = $dir;
                    }
                }
            }
        }
        $this->users = $users;

        $artists = [];
        $path = env("PATH_MUSIC");
        if (is_dir($path)) {
            $dirs = scandir($path);
            foreach ($dirs as $dir) {
                if ($dir !== '.' && $dir !== '..' && is_dir($path . "/" . $dir)) {
                    $artists[] = $dir;
                }
            }
        }
        $this->artists = $artists;
    }

    public static function canView(): bool
    {
        return env("SPOTIFY_SH")!='' && env("LIDARR_API")!='';
    }

    public function getHeaderWidgets(): array
    {
        return [
            SpotdlTable::make(),
        ];
    }
}
