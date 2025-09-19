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
