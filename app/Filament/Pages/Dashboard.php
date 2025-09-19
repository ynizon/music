<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\IpTable;
use App\Filament\Widgets\UserTable;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.dashboard';
    protected static ?string $title = 'Tableau de bord';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = false;

    public function getHeaderWidgets(): array
    {
        return [
            UserTable::make(),
            IpTable::make(),
        ];
    }
}
